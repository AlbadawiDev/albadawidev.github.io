#!/usr/bin/env python3
"""Build a reproducible installable plugin ZIP. No client data or test database."""
import argparse
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("--output", type=Path, required=True)
args = parser.parse_args()
source = Path(__file__).resolve().parents[1] / "reference-cards"
args.output.parent.mkdir(parents=True, exist_ok=True)
with ZipFile(args.output, "w", compression=ZIP_DEFLATED) as package:
    for path in sorted(source.rglob("*")):
        if path.is_file():
            info = ZipInfo(path.relative_to(source.parent).as_posix(), (1980, 1, 1, 0, 0, 0))
            info.compress_type = ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            package.writestr(info, path.read_bytes())
print(f"Plugin ZIP: {args.output}")
