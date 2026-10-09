"""Check portable static pages without network dependencies."""
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import urlsplit, unquote
import hashlib, json

ROOT=Path(__file__).resolve().parents[1]
MEDIA_SHA='cdd4c4d8b913f198510cb7146c69cdcf924532237077bb5588bd9d97347c273c'
# The interactive browser demo is written in Spanish; case studies remain English.
PAGE_LANGUAGES={'casepilot-demo.html':'es'}
class Page(HTMLParser):
    def __init__(self):
        super().__init__();self.ids=[];self.links=[];self.h1=0;self.main=0;self.lang=None;self.errors=[]
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if 'id' in a:self.ids.append(a['id'])
        if tag=='h1':self.h1+=1
        if tag=='main':self.main+=1
        if tag=='html':self.lang=a.get('lang')
        if tag=='img' and not a.get('alt'):self.errors.append('image missing description')
        if tag=='video' and not a.get('aria-label'):self.errors.append('video missing accessible name')
        for k in ('href','src','poster'):
            if k in a:self.links.append(a[k])

pages={}
for path in sorted(ROOT.glob('*.html')):
    p=Page();p.feed(path.read_text(encoding='utf-8'));pages[path]=p
errors=[]
for path,p in pages.items():
    expected_lang=PAGE_LANGUAGES.get(path.name,'en')
    if p.h1!=1 or p.main!=1 or p.lang!=expected_lang:errors.append(f'{path.name}: expected one h1, one main, lang={expected_lang}')
    if len(p.ids)!=len(set(p.ids)):errors.append(f'{path.name}: duplicate IDs')
    errors.extend(f'{path.name}: {x}' for x in p.errors)
    for link in p.links:
        u=urlsplit(link)
        if u.scheme or u.netloc:continue
        target=(path.parent/unquote(u.path)).resolve() if u.path else path
        if ROOT not in target.parents and target!=ROOT:errors.append(f'{path.name}: path escapes site: {link}');continue
        if not target.is_file():errors.append(f'{path.name}: missing file: {link}');continue
        if u.fragment and target in pages and unquote(u.fragment) not in pages[target].ids:errors.append(f'{path.name}: missing fragment: {link}')
if hashlib.sha256((ROOT/'assets/udo-demo.mp4').read_bytes()).hexdigest()!=MEDIA_SHA:errors.append('demo media SHA mismatch')
if not (ROOT/'assets/udo-captions-en.vtt').read_text().startswith('WEBVTT'):errors.append('invalid captions header')
print(json.dumps({'pages':len(pages),'errors':errors,'media_sha256':MEDIA_SHA,'passed':not errors},indent=2))
raise SystemExit(bool(errors))
