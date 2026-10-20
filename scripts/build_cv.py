"""Generate Daniel's verified, bilingual, single-column CVs.

No client source or identity-document number belongs in this public artifact.
The course credential is distinct from a standardized language assessment.
"""
from pathlib import Path
from html import escape
import json

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, HRFlowable, KeepTogether,
)
from pypdf import PdfReader

BASE = Path(__file__).resolve().parents[1]
OUT = BASE / 'assets' / 'cv'
OUT.mkdir(parents=True, exist_ok=True)
for name, filename in (
    ('CVSans', 'DejaVuSans.ttf'), ('CVBold', 'DejaVuSans-Bold.ttf'),
):
    pdfmetrics.registerFont(TTFont(name, '/usr/share/fonts/truetype/dejavu/' + filename))
pdfmetrics.registerFontFamily('CVSans', normal='CVSans', bold='CVBold')

INK = colors.HexColor('#162D3A')
BLUE = colors.HexColor('#185A78')
GRAY = colors.HexColor('#465560')
RULE = colors.HexColor('#BDC9CF')
S = {
    'name': ParagraphStyle('name', fontName='CVBold', fontSize=19.8, leading=24,
                           textColor=INK, spaceAfter=3),
    'title': ParagraphStyle('title', fontName='CVBold', fontSize=10.6, leading=14.5,
                            textColor=BLUE, spaceAfter=5),
    'meta': ParagraphStyle('meta', fontName='CVSans', fontSize=8.7, leading=12,
                           textColor=GRAY, spaceAfter=2),
    'section': ParagraphStyle('section', fontName='CVBold', fontSize=9.3, leading=13,
                              textColor=BLUE, spaceBefore=10, spaceAfter=4),
    'sub': ParagraphStyle('sub', fontName='CVBold', fontSize=9.4, leading=13,
                          textColor=INK, spaceAfter=2),
    'body': ParagraphStyle('body', fontName='CVSans', fontSize=9.3, leading=13,
                           textColor=INK, spaceAfter=4),
    'bullet': ParagraphStyle('bullet', fontName='CVSans', fontSize=9.3, leading=13,
                             textColor=INK, leftIndent=9, firstLineIndent=-9,
                             spaceAfter=3),
}

LINKS = {
    'portfolio': 'https://albadawidev.github.io/',
    'github': 'https://github.com/AlbadawiDev',
    'linkedin': 'https://www.linkedin.com/in/daniel-samir-al-badawi-aazar-1b8459368/',
    'udo': 'https://albadawidev.github.io/udo.html',
    'casepilot': 'https://github.com/AlbadawiDev/casepilot',
    'paysub': 'https://github.com/AlbadawiDev/PaySub',
    'voxgig': 'https://github.com/AlbadawiDev/voxgig-frankfurter-sdk',
    'wordpress': 'https://albadawidev.github.io/wordpress.html',
}

C = {
    'EN': {
        'title': 'Junior Software Developer | PHP/Laravel, Python & Functional QA',
        'availability': 'Cumaná, Venezuela (UTC-4) | Remote | Schedule agreed around university studies',
        'profile_heading': 'PROFILE',
        'profile': 'Advanced Informatics undergraduate building web applications with role-based workflows, SQL data models and reproducible tests. Paid work includes WordPress customization and two Voxgig technical exercises. Seeking junior development, web support or functional QA work.',
        'paid_heading': 'PAID ASSIGNMENTS',
        'wp_title': 'WordPress Plugin Customization | First Cognition via Upwork',
        'wp_date': 'November 2025 - January 2026 | One freelance assignment',
        'wp_desc': "Adapted a blog and articles plugin to the client's reference design. Public portfolio includes a separate PHP/WordPress lab with documented integration checks.",
        'vox_title': 'SDK Generation & Technical Review | Voxgig Ltd',
        'vox_date': 'October 2026 | Two paid technical exercises',
        'vox_desc': 'Generated an unofficial TypeScript SDK, verified its generated suite and API behavior, and delivered a reproducible Jostraca review. Source and developer experience report available.',
        'other': 'Additional freelance work: three Photoshop photo-editing assignments through Upwork.',
        'projects_heading': 'SELECTED ACADEMIC & PERSONAL PROJECTS',
        'projects': [
            ('UDO Thesis Management | Java 21, Spring Boot, JPA, SQL',
             'Academic workflow from proposal to committee review and defense scheduling. Role permissions, document versions and activity history; synthetic demo and verification evidence.',
             'udo', 'View case study'),
            ('CasePilot | Python, SQLite, JavaScript',
             'Support-ticket application with role permissions, edit-conflict detection and transactional CSV imports. HTTP and frontend regressions verify concurrent edits and preserve user drafts.',
             'casepilot', 'View source and tests'),
            ('PaySub | PHP, Laravel, React, SQL',
             'Subscription plans and manual bank-transfer review with API role/ownership checks and private receipts. Tested portfolio demo; no live payment gateway.',
             'paysub', 'View source and tests'),
        ],
        'skills_heading': 'TECHNICAL SKILLS',
        'skills': [
            'Web: PHP, Laravel, WordPress, Python, Flask, Django, JavaScript, React, HTML/CSS.',
            'Data & delivery: SQL (MySQL, PostgreSQL, SQLite), Git, REST APIs, regression testing.',
            'Functional QA: reproduce defects, check permissions and validation, document expected vs. actual behavior.',
        ],
        'education_heading': 'EDUCATION & LANGUAGES',
        'education': 'Licenciatura en Informática | Universidad de Oriente, Núcleo Sucre',
        'education_date': '2022 - present | Advanced undergraduate; degree in progress.',
        'course': 'English Level III (CEFR B1) | American English Center',
        'course_date': '72-hour course completed and passed | 25 July 2026.',
        'languages': 'Spanish: native | English: B1 | Arabic: spoken; limited writing',
    },
    'ES': {
        'title': 'Desarrollador de software junior | PHP/Laravel, Python y QA funcional',
        'availability': 'Cumaná, Venezuela (UTC-4) | Remoto | Horario a acordar con los estudios universitarios',
        'profile_heading': 'PERFIL',
        'profile': 'Estudiante avanzado de Informática con proyectos web de flujos por roles, modelos SQL y pruebas reproducibles. Encargos pagados de personalización WordPress y dos ejercicios técnicos para Voxgig. Busco aportar en desarrollo junior, soporte web o QA funcional.',
        'paid_heading': 'ENCARGOS REMUNERADOS',
        'wp_title': 'Personalización de plugin WordPress | First Cognition por Upwork',
        'wp_date': 'Noviembre de 2025 - enero de 2026 | Un encargo freelance',
        'wp_desc': 'Adapté un plugin de blog y artículos al diseño de referencia del cliente. El portafolio incluye un laboratorio separado de PHP/WordPress con pruebas de integración documentadas.',
        'vox_title': 'Generación de SDK y revisión técnica | Voxgig Ltd',
        'vox_date': 'Octubre de 2026 | Dos ejercicios técnicos remunerados',
        'vox_desc': 'Generé un SDK TypeScript no oficial, comprobé su suite generada y la API, y entregué una revisión reproducible de Jostraca. Código e informe de experiencia de desarrollo disponibles.',
        'other': 'Otros encargos freelance: tres trabajos de edición de fotografías con Photoshop en Upwork.',
        'projects_heading': 'PROYECTOS ACADÉMICOS Y PERSONALES DESTACADOS',
        'projects': [
            ('Gestión de Trabajos de Grado UDO | Java 21, Spring Boot, JPA, SQL',
             'Flujo académico desde la propuesta hasta la revisión y programación de defensa. Permisos por rol, versiones de documentos e historial; demo con datos ficticios y evidencia de pruebas.',
             'udo', 'Ver caso de estudio'),
            ('CasePilot | Python, SQLite, JavaScript',
             'Gestión de tickets con permisos por rol, detección de conflictos de edición e importación CSV transaccional. Pruebas HTTP y de interfaz comprueban ediciones simultáneas y conservación de borradores.',
             'casepilot', 'Ver código y pruebas'),
            ('PaySub | PHP, Laravel, React, SQL',
             'Planes de suscripción y revisión manual de transferencias con permisos en la API y recibos privados. Demo de portafolio con pruebas; sin pasarela de pago real.',
             'paysub', 'Ver código y pruebas'),
        ],
        'skills_heading': 'HABILIDADES TÉCNICAS',
        'skills': [
            'Web: PHP, Laravel, WordPress, Python, Flask, Django, JavaScript, React, HTML/CSS.',
            'Datos y entrega: SQL (MySQL, PostgreSQL, SQLite), Git, API REST y pruebas de regresión.',
            'QA funcional: reproducir errores, comprobar permisos y validaciones, documentar resultados.',
        ],
        'education_heading': 'FORMACIÓN E IDIOMAS',
        'education': 'Licenciatura en Informática | Universidad de Oriente, Núcleo Sucre',
        'education_date': '2022 - actualidad | Estudiante avanzado; carrera en curso.',
        'course': 'Inglés Nivel III (CEFR B1) | American English Center',
        'course_date': 'Programa de 72 horas cursado y aprobado | 25 de julio de 2026.',
        'languages': 'Español: nativo | Inglés: B1 | Árabe: oral; escritura limitada',
    },
}


def para(text, style='body'):
    return Paragraph(escape(text), S[style])


def link(key, label):
    return '<link href="' + escape(LINKS[key], quote=True) + '" color="#185A78">' + escape(label) + '</link>'


def heading_with_link(text, key, label):
    return Paragraph(escape(text) + ' | ' + link(key, label), S['sub'])


def footer(canvas, doc):
    canvas.saveState()
    canvas.setStrokeColor(RULE)
    canvas.setLineWidth(.4)
    canvas.line(39, 26, A4[0] - 39, 26)
    canvas.setFont('CVSans', 7.2)
    canvas.setFillColor(GRAY)
    canvas.drawString(39, 15, 'Daniel Al Badawi | 20 October 2026')
    canvas.restoreState()


def build(lang, c):
    stem = f'Daniel_Albadawi_CV_{lang}_2026-10-20'
    target = OUT / (stem + '.pdf')
    doc = SimpleDocTemplate(str(target), pagesize=A4, leftMargin=39, rightMargin=39,
                            topMargin=27, bottomMargin=37,
                            title='Daniel Al Badawi - Junior Software Developer - ' + lang,
                            author='Daniel Samir Al Badawi Aazar')
    story = [
        para('Daniel Samir Al Badawi Aazar', 'name'),
        para(c['title'], 'title'), para(c['availability'], 'meta'),
        Paragraph('<link href="mailto:albadawi.dev@gmail.com" color="#185A78">albadawi.dev@gmail.com</link> | +58 424 836 0302', S['meta']),
        Paragraph(' | '.join(link(k, label) for k, label in
                  [('portfolio', 'Portfolio'), ('github', 'GitHub'), ('linkedin', 'LinkedIn')]), S['meta']),
        Spacer(1, 4), HRFlowable(width='100%', thickness=.6, color=RULE),
        para(c['profile_heading'], 'section'), para(c['profile']),
        para(c['paid_heading'], 'section'),
        heading_with_link(c['wp_title'], 'wordpress', 'Portfolio'),
        para(c['wp_date'], 'meta'), para(c['wp_desc']),
        heading_with_link(c['vox_title'], 'voxgig', 'GitHub'),
        para(c['vox_date'], 'meta'), para(c['vox_desc']), para(c['other'], 'meta'),
        para(c['projects_heading'], 'section'),
    ]
    for name, desc, key, label in c['projects']:
        story.append(KeepTogether([
            para(name, 'sub'),
            Paragraph(escape(desc) + ' ' + link(key, label) + '.', S['body']),
        ]))
    story.append(para(c['skills_heading'], 'section'))
    for item in c['skills']:
        story.append(para(item))
    story.extend([
        para(c['education_heading'], 'section'), para(c['education'], 'sub'),
        para(c['education_date'], 'meta'), para(c['course'], 'sub'),
        para(c['course_date'], 'meta'), para(c['languages']),
    ])
    doc.build(story, onFirstPage=footer, onLaterPages=footer)
    reader = PdfReader(target)
    text = '\n'.join(p.extract_text() for p in reader.pages)
    (OUT / (stem + '.txt')).write_text(text + '\n', encoding='utf-8')
    assert len(reader.pages) == 1, (lang, len(reader.pages))
    assert 'American English Center' in text and 'B1' in text and '72' in text
    assert ('degree in progress' in text if lang == 'EN' else 'carrera en curso' in text)
    assert '30.918.302' not in text and 'B2' not in text and 'C1' not in text
    links = [a.get_object().get('/A', {}).get('/URI')
             for pg in reader.pages for a in pg.get('/Annots', [])]
    result = {'language': lang, 'pages': len(reader.pages), 'path': str(target),
              'links': [x for x in links if x], 'characters': len(text), 'words': len(text.split())}
    print(json.dumps(result, ensure_ascii=False))
    return result


if __name__ == '__main__':
    results = [build(lang, c) for lang, c in C.items()]
    (OUT / 'CV_validation_20261020.json').write_text(json.dumps(results, ensure_ascii=False, indent=2) + '\n')
