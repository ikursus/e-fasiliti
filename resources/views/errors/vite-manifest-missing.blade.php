<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aset frontend belum dibina</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; padding: 2.5rem 1.25rem; background: #f8fafc; color: #0f172a;
               font-family: ui-sans-serif, system-ui, "Segoe UI", sans-serif; line-height: 1.6; }
        .card { max-width: 40rem; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0;
                border-radius: 0.75rem; padding: 2rem; }
        h1 { margin: 0 0 0.5rem; font-size: 1.25rem; }
        p { margin: 0 0 1rem; color: #475569; }
        ol { margin: 0 0 1rem; padding-left: 1.25rem; color: #475569; }
        code, pre { font-family: ui-monospace, "Cascadia Code", Consolas, monospace; font-size: 0.875rem; }
        pre { background: #0f172a; color: #e2e8f0; padding: 0.75rem 1rem; border-radius: 0.5rem;
              overflow-x: auto; margin: 0.25rem 0 1rem; }
        .note { font-size: 0.8125rem; color: #64748b; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Aset frontend belum dibina</h1>
        <p>Fail <code>public/build/manifest.json</code> tiada, jadi Vite tidak dapat memuatkan CSS dan JS.
           Ini biasanya berlaku selepas <em>clone</em> atau <em>pull</em> yang baharu, kerana folder
           <code>public/build</code> tidak disimpan dalam repositori.</p>
        <ol>
            <li>Pastikan Node.js 20 ke atas dipasang.</li>
            <li>Jalankan arahan di bawah dari akar projek.</li>
            <li>Muat semula halaman ini.</li>
        </ol>
        <pre>composer setup</pre>
        <p>Atau, jika kebergantungan sudah lengkap:</p>
        <pre>npm install
npm run build</pre>
        <p class="note">Semasa membangun, <code>npm run dev</code> juga memadai kerana ia menyediakan
           pelayan Vite dan mencipta fail <code>public/hot</code>.</p>
    </div>
</body>
</html>
