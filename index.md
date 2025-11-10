<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Daniel Samir Al Badawi — Portafolio</title>
  <style>
    :root { --fg:#111; --muted:#555; --bg:#fff; --card:#f7f7f7; --link:#2563eb; }
    *{box-sizing:border-box} body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial,sans-serif;color:var(--fg);background:var(--bg);line-height:1.5}
    .wrap{max-width:900px;margin:0 auto;padding:24px}
    header{padding:12px 0 8px;border-bottom:1px solid #eee}
    h1{margin:0 0 6px;font-size:28px}
    .tag{color:var(--muted);margin:0 0 12px}
    .btns a{display:inline-block;margin-right:8px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;text-decoration:none;color:var(--fg)}
    .btns a.primary{border-color:var(--link);color:var(--link)}
    section{margin:28px 0}
    h2{font-size:20px;margin:0 0 12px}
    .grid{display:grid;gap:12px}
    .grid.cols-2{grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
    .card{background:var(--card);padding:14px;border-radius:12px}
    ul{margin:8px 0 0 18px}
    a{color:var(--link)}
    footer{margin:32px 0 12px;color:var(--muted);font-size:14px}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Daniel Samir Al Badawi Aazar</h1>
      <p class="tag">QA manual · Arreglos WordPress · Automatizaciones no-code (Zapier/Make) · Remoto LATAM</p>
      <div class="btns">
        <a class="primary" href="mailto:albadawi.dev@gmail.com">Contactar</a>
        <a href="https://www.linkedin.com/in/daniel-albadawi-dev-1b8459368/" target="_blank" rel="noopener">LinkedIn</a>
        <a href="https://github.com/AlbadawiDev" target="_blank" rel="noopener">GitHub</a>
      </div>
    </header>

    <section>
      <h2>Qué hago</h2>
      <div class="grid cols-2">
        <div class="card">
          <strong>QA express 24h</strong>
          <ul>
            <li>Pruebas funcionales con checklist</li>
            <li>Reporte claro con evidencias</li>
          </ul>
        </div>
        <div class="card">
          <strong>WordPress fixes</strong>
          <ul>
            <li>Velocidad, formularios, migraciones</li>
            <li>Entrega con métricas antes/después</li>
          </ul>
        </div>
        <div class="card">
          <strong>Automatizaciones no-code</strong>
          <ul>
            <li>Zapier/Make ↔ Sheets/CRM/Email</li>
            <li>Flujos simples y documentados</li>
          </ul>
        </div>
      </div>
    </section>

    <section>
      <h2>Proyectos destacados</h2>
      <div class="grid">
        <div class="card">
          <strong>VeriCred — certificados con QR (Django)</strong><br/>
          <a href="https://github.com/AlbadawiDev/vericred" target="_blank" rel="noopener">Repositorio</a>
        </div>
        <div class="card">
          <strong>AWS Pipeline — S3 → Lambda → Athena (Terraform)</strong><br/>
          <a href="https://github.com/AlbadawiDev/aws-pipeline" target="_blank" rel="noopener">Repositorio</a>
        </div>
        <div class="card">
          <strong>SharingApp (Android/Java) — Contactos y préstamos</strong><br/>
          <a href="https://github.com/AlbadawiDev/SharingApp-Contacts-Borrowed" target="_blank" rel="noopener">Repositorio</a>
        </div>
      </div>
    </section>

    <section>
      <h2>Más repos</h2>
      <ul>
        <li><a href="https://github.com/AlbadawiDev/pro-path-2025" target="_blank" rel="noopener">pro-path-2025</a></li>
        <li><a href="https://github.com/AlbadawiDev/mi-calculadora-python" target="_blank" rel="noopener">mi-calculadora-python</a></li>
        <li><a href="https://github.com/AlbadawiDev/practica-edad" target="_blank" rel="noopener">practica-edad</a>,
            <a href="https://github.com/AlbadawiDev/practica-paridad" target="_blank" rel="noopener">paridad</a>,
            <a href="https://github.com/AlbadawiDev/practica-contador" target="_blank" rel="noopener">contador</a></li>
      </ul>
    </section>

    <section>
      <h2>Certificaciones</h2>
      <ul>
        <li>AWS Cloud Practitioner Essentials — AWS (Oct 2025)</li>
        <li>Object-Oriented Design — Univ. of Alberta (Sep 2025)</li>
        <li>Foundations: Data, Data, Everywhere — Google (Sep 2025)</li>
        <li>Introducción a Git y GitHub — Google (Jun 2025)</li>
        <li>Programming for Everybody (Python) — Univ. of Michigan (Jun 2025)</li>
      </ul>
    </section>

    <footer>
      © <span id="y"></span> Daniel Samir Al Badawi · Cumaná, Venezuela
    </footer>
  </div>
  <script>document.getElementById('y').textContent=new Date().getFullYear()</script>
</body>
</html>
