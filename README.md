# Daniel Al Badawi — portfolio

A static English portfolio with four evidence-based case studies: UDO Thesis Management, CasePilot, VeriCred and PaySub. Every screenshot and the UDO video use fictional data from locally executed applications. Project pages distinguish verified behavior from deployment limitations.

## Preview

From this directory, run `python -m http.server 8090 --bind 127.0.0.1` and open <http://127.0.0.1:8090>. No package installation or build step is required.

## Curriculum vitae

Download the current CV for WordPress, web support and junior QA opportunities:

- [English CV (PDF)](assets/cv/Daniel_Albadawi_CV_EN_WordPress_QA_2026-10-08.pdf)
- [CV en español (PDF)](assets/cv/Daniel_Albadawi_CV_ES_WordPress_QA_2026-10-08.pdf)

## Verification

`python scripts/check_site.py` validates internal files and fragments, unique IDs, one main heading per page, accessibility names, image descriptions, video captions and the committed demo media hash. Browser review covers desktop and 390px mobile layouts, project navigation and the silent video.

The video is an edited walkthrough built from real application screenshots, not a continuous screen recording. It contains no audio. It is separate from CasePilot's original downloaded video.

## Publishing

GitHub Pages uses the repository's configured publishing source. The reviewed portfolio update in [PR #1](https://github.com/AlbadawiDev/albadawidev.github.io/pull/1) is merged into `main`, and its Pages deployment completed successfully. Further updates go through pull requests and the existing static checks. No institutional database, private account lists, environment files or credentials belong in this repository.
