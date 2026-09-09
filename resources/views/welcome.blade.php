<!DOCTYPE html>
<html lang="ms" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A1522">

    <title>SPFA — Sistem Pengurusan Fasiliti &amp; Aset ICT</title>
    <meta name="description" content="SPFA menyatukan tempahan bilik mesyuarat dan penyelenggaraan aset ICT dalam satu sistem berpusat: kalendar tanpa pertindihan, tiket kerosakan bernombor rujukan, inventari beraset label QR, dan laporan SLA automatik.">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=fraunces:400,600,700|ibm-plex-mono:400,500|ibm-plex-sans:400,500,600">

    <script>document.documentElement.className = 'js';</script>

    <style>
        /* ============================================================
           SPFA — Landing Page
           Arah reka bentuk: "Blueprint Institusi"
           Lukisan teknikal · navy dakwat · kertas suam · aksen tembaga
           ============================================================ */

        *, *::before, *::after { box-sizing: border-box; }
        * { margin: 0; padding: 0; }

        :root {
            /* Permukaan */
            --ink:        #0A1522;
            --ink-2:      #10243A;
            --ink-3:      #17334F;
            --paper:      #F5F3EE;
            --paper-2:    #FFFFFF;
            --paper-3:    #EAE6DD;

            /* Teks */
            --on-ink:     #E9E5DB;
            --on-ink-dim: #96A6B8;
            --on-paper:   #101E2E;
            --on-paper-dim:#5A6675;

            /* Aksen */
            --brass:      #C08A2E;
            --brass-soft: #E3B45F;
            --brass-ink:  #8A5F14;
            --steel:      #2C6E9B;

            /* Garisan */
            --line-ink:   rgba(233, 229, 219, .16);
            --line-paper: #D8D3C7;

            /* Status (rujuk UI/UX spec 5.1) */
            --st-wait:    #A47708;
            --st-active:  #1F6FB2;
            --st-done:    #1F7A4D;
            --st-warn:    #C2610F;
            --st-danger:  #B3261E;
            --st-neutral: #6B7280;

            /* Tipografi */
            --f-display: 'Fraunces', 'Iowan Old Style', Georgia, 'Times New Roman', serif;
            --f-body:    'IBM Plex Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
            --f-mono:    'IBM Plex Mono', ui-monospace, 'Cascadia Mono', Consolas, monospace;

            /* Rentak jarak — gandaan 4px (rujuk UI/UX spec 5.3) */
            --gap:   16px;
            --gap-2: 32px;
            --gap-3: 64px;
            --pad-section: clamp(64px, 9vw, 128px);
            --radius: 6px;

            /* Tekstur */
            --grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }

        body {
            font-family: var(--f-body);
            font-size: 16px;
            line-height: 1.65;
            color: var(--on-paper);
            background: var(--paper);
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }

        img, svg { display: block; max-width: 100%; }
        img { height: auto; }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; color: inherit; background: none; border: 0; cursor: pointer; }
        ul { list-style: none; }

        ::selection { background: var(--brass); color: var(--ink); }

        :focus-visible {
            outline: 2px solid var(--brass);
            outline-offset: 3px;
            border-radius: 2px;
        }

        /* ---------- Susun atur asas ---------- */

        .wrap {
            width: min(1180px, calc(100% - 2 * clamp(20px, 5vw, 56px)));
            margin-inline: auto;
        }

        .section { padding-block: var(--pad-section); position: relative; }

        /* Elak tajuk tersembunyi di bawah bar navigasi tetap */
        section[id], main[id] { scroll-margin-top: 88px; }

        .section--ink {
            background: var(--ink);
            color: var(--on-ink);
        }

        .section--paper-2 { background: var(--paper-2); }

        /* Garisan grid blueprint */
        .blueprint::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(to right, var(--grid-c, rgba(255,255,255,.045)) 1px, transparent 1px),
                linear-gradient(to bottom, var(--grid-c, rgba(255,255,255,.045)) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 30%, #000 30%, transparent 100%);
        }

        .blueprint--paper { --grid-c: rgba(16, 30, 46, .05); }

        /* Bunyi bijian halus */
        .grain::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image: var(--grain);
            opacity: .035;
            mix-blend-mode: overlay;
        }

        /* ---------- Tipografi ---------- */

        h1, h2, h3, h4 {
            font-family: var(--f-display);
            font-weight: 600;
            line-height: 1.12;
            letter-spacing: -.015em;
        }

        .display {
            font-size: clamp(40px, 6.4vw, 76px);
            font-weight: 600;
            letter-spacing: -.03em;
            line-height: 1.04;
        }

        .h2 {
            font-size: clamp(30px, 4vw, 46px);
            letter-spacing: -.025em;
        }

        .h3 { font-size: clamp(19px, 1.6vw, 22px); letter-spacing: -.01em; }

        .lead {
            font-size: clamp(17px, 1.35vw, 19px);
            line-height: 1.7;
            color: var(--on-paper-dim);
            max-width: 62ch;
        }

        .section--ink .lead { color: var(--on-ink-dim); }

        .accent { color: var(--brass-ink); font-style: italic; font-weight: 400; }
        .section--ink .accent { color: var(--brass-soft); }

        /* Eyebrow monospace bernombor */
        .eyebrow {
            font-family: var(--f-mono);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--brass-ink);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .section--ink .eyebrow { color: var(--brass-soft); }

        .eyebrow::after {
            content: '';
            height: 1px;
            width: clamp(32px, 6vw, 88px);
            background: currentColor;
            opacity: .45;
        }

        .section-head { max-width: 74ch; margin-bottom: clamp(40px, 5vw, 68px); }
        .section-head .lead { margin-top: 20px; }

        /* ---------- Butang ---------- */

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 22px;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -.005em;
            border: 1px solid transparent;
            transition: transform .25s cubic-bezier(.2,.8,.2,1), background-color .25s, border-color .25s, color .25s, box-shadow .25s;
            will-change: transform;
        }

        .btn svg { width: 17px; height: 17px; flex: none; transition: transform .3s cubic-bezier(.2,.8,.2,1); }
        .btn:hover svg { transform: translateX(3px); }

        .btn--primary {
            background: var(--brass);
            color: #17100A;
            box-shadow: 0 1px 0 rgba(255,255,255,.28) inset, 0 8px 22px -12px rgba(192,138,46,.9);
        }
        .btn--primary:hover { background: var(--brass-soft); transform: translateY(-2px); }

        .btn--ghost-ink {
            border-color: var(--line-ink);
            color: var(--on-ink);
            background: rgba(255,255,255,.03);
        }
        .btn--ghost-ink:hover { border-color: rgba(233,229,219,.4); background: rgba(255,255,255,.07); transform: translateY(-2px); }

        .btn--ghost {
            border-color: var(--line-paper);
            color: var(--on-paper);
            background: var(--paper-2);
        }
        .btn--ghost:hover { border-color: var(--on-paper); transform: translateY(-2px); }

        .btn--sm { padding: 9px 16px; font-size: 14px; }

        /* ---------- Navigasi ---------- */

        .nav {
            position: fixed;
            inset: 0 0 auto;
            z-index: 100;
            transition: background-color .35s, border-color .35s, backdrop-filter .35s;
            border-bottom: 1px solid transparent;
        }

        .nav.is-stuck {
            background: rgba(10, 21, 34, .82);
            backdrop-filter: saturate(150%) blur(14px);
            -webkit-backdrop-filter: saturate(150%) blur(14px);
            border-bottom-color: var(--line-ink);
        }

        .nav__inner {
            display: flex;
            align-items: center;
            gap: var(--gap-2);
            height: 74px;
            color: var(--on-ink);
        }

        .brand { display: flex; align-items: center; gap: 12px; flex: none; }

        .brand__mark {
            width: 38px; height: 38px;
            display: grid; place-items: center;
            border: 1px solid rgba(192,138,46,.55);
            border-radius: var(--radius);
            background: linear-gradient(150deg, rgba(192,138,46,.24), rgba(192,138,46,.04));
            font-family: var(--f-mono);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: .04em;
            color: var(--brass-soft);
            flex: none;
        }

        .brand__text { line-height: 1.15; }
        .brand__name {
            display: block;
            font-family: var(--f-display);
            font-size: 19px;
            font-weight: 600;
            letter-spacing: -.01em;
        }
        .brand__sub {
            display: block;
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: var(--on-ink-dim);
        }

        .nav__links { display: flex; gap: 6px; margin-left: auto; }

        .nav__links a {
            position: relative;
            white-space: nowrap;
            padding: 8px 12px;
            font-size: 14.5px;
            color: var(--on-ink-dim);
            border-radius: 4px;
            transition: color .25s;
        }
        .nav__links a::after {
            content: '';
            position: absolute;
            left: 12px; right: 12px; bottom: 3px;
            height: 1px;
            background: var(--brass);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s cubic-bezier(.2,.8,.2,1);
        }
        .nav__links a:hover { color: var(--on-ink); }
        .nav__links a:hover::after { transform: scaleX(1); }

        .nav__cta { display: flex; align-items: center; gap: 10px; flex: none; }

        .nav__burger {
            display: none;
            width: 42px; height: 42px;
            border: 1px solid var(--line-ink);
            border-radius: var(--radius);
            place-items: center;
            color: var(--on-ink);
        }
        .nav__burger svg { width: 20px; height: 20px; }

        .nav__panel {
            display: none;
            border-top: 1px solid var(--line-ink);
            background: rgba(10,21,34,.97);
            backdrop-filter: blur(14px);
            padding: 18px 0 26px;
        }
        .nav__panel.is-open { display: block; }
        .nav__panel a {
            display: block;
            padding: 13px 4px;
            font-size: 16px;
            color: var(--on-ink);
            border-bottom: 1px solid var(--line-ink);
        }
        .nav__panel .btn { margin-top: 18px; width: 100%; justify-content: center; }

        /* ---------- Hero ---------- */

        .hero {
            position: relative;
            background: var(--ink);
            color: var(--on-ink);
            padding-top: clamp(128px, 15vw, 176px);
            padding-bottom: clamp(72px, 9vw, 112px);
            overflow: hidden;
        }

        .hero__glow {
            position: absolute;
            width: 900px; height: 900px;
            top: -420px; right: -220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(44,110,155,.30), rgba(44,110,155,0) 62%);
            pointer-events: none;
            animation: drift 22s ease-in-out infinite alternate;
        }
        .hero__glow--2 {
            top: auto; bottom: -560px; right: auto; left: -300px;
            width: 780px; height: 780px;
            background: radial-gradient(circle, rgba(192,138,46,.20), rgba(192,138,46,0) 62%);
            animation-duration: 28s;
            animation-direction: alternate-reverse;
        }

        @keyframes drift {
            from { transform: translate3d(0,0,0) scale(1); }
            to   { transform: translate3d(-60px, 50px, 0) scale(1.12); }
        }

        .hero__grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(0, .98fr);
            gap: clamp(40px, 5vw, 72px);
            align-items: center;
        }

        .hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 7px 14px 7px 10px;
            border: 1px solid var(--line-ink);
            border-radius: 999px;
            background: rgba(255,255,255,.04);
            font-family: var(--f-mono);
            font-size: 11.5px;
            letter-spacing: .11em;
            text-transform: uppercase;
            color: var(--on-ink-dim);
            margin-bottom: 26px;
        }

        .hero__badge b { color: var(--brass-soft); font-weight: 500; }

        .pulse {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--st-done);
            box-shadow: 0 0 0 0 rgba(31,122,77,.7);
            animation: pulse 2.4s infinite;
            flex: none;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(31,122,77,.65); }
            70%  { box-shadow: 0 0 0 9px rgba(31,122,77,0); }
            100% { box-shadow: 0 0 0 0 rgba(31,122,77,0); }
        }

        .hero h1 { margin-bottom: 24px; }
        .hero .lead { margin-bottom: 34px; }

        .hero__actions { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 44px; }

        .hero__facts {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            border-top: 1px solid var(--line-ink);
            padding-top: 26px;
        }
        .hero__facts li { padding-right: 32px; margin-right: 32px; border-right: 1px solid var(--line-ink); }
        .hero__facts li:last-child { border-right: 0; margin-right: 0; padding-right: 0; }
        .hero__facts b {
            display: block;
            font-family: var(--f-display);
            font-size: 30px;
            font-weight: 600;
            letter-spacing: -.02em;
            line-height: 1.1;
        }
        .hero__facts span {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--on-ink-dim);
        }

        /* ---------- Mockup produk (hero) ---------- */

        .mock { position: relative; }

        .mock__app {
            background: #0E1E30;
            border: 1px solid rgba(233,229,219,.14);
            border-radius: 10px;
            box-shadow: 0 40px 80px -40px rgba(0,0,0,.85), 0 0 0 1px rgba(255,255,255,.03) inset;
            overflow: hidden;
            transform: perspective(1600px) rotateY(-7deg) rotateX(2.5deg);
            transition: transform .7s cubic-bezier(.2,.8,.2,1);
        }
        .mock:hover .mock__app { transform: perspective(1600px) rotateY(-2deg) rotateX(1deg); }

        .mock__bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 14px;
            border-bottom: 1px solid rgba(233,229,219,.1);
            background: rgba(255,255,255,.025);
        }
        .mock__dot { width: 9px; height: 9px; border-radius: 50%; background: rgba(233,229,219,.2); }
        .mock__title {
            margin-left: 8px;
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .09em;
            color: var(--on-ink-dim);
        }

        .mock__body { padding: 16px; }

        .mock__filters {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .mock__field {
            border: 1px solid rgba(233,229,219,.12);
            border-radius: 4px;
            padding: 8px 10px;
            background: rgba(255,255,255,.02);
        }
        .mock__field i {
            display: block;
            font-family: var(--f-mono);
            font-style: normal;
            font-size: 9px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #6E8299;
            margin-bottom: 3px;
        }
        .mock__field b { font-size: 12.5px; font-weight: 500; color: #D7E2ED; }

        /* Jalur ketersediaan */
        .mock__strip { margin-bottom: 14px; }
        .mock__strip-head {
            display: flex; justify-content: space-between;
            font-family: var(--f-mono); font-size: 9.5px;
            letter-spacing: .1em; color: #6E8299;
            margin-bottom: 6px;
        }
        .mock__slots { display: grid; grid-template-columns: repeat(12, 1fr); gap: 3px; }
        .mock__slot {
            height: 26px;
            border-radius: 2px;
            background: rgba(255,255,255,.06);
            animation: slotIn .5s cubic-bezier(.2,.8,.2,1) backwards;
        }
        .mock__slot--busy { background: rgba(44,110,155,.55); }
        .mock__slot--pick {
            background: var(--brass);
            box-shadow: 0 0 0 1px rgba(227,180,95,.6), 0 0 16px rgba(192,138,46,.45);
        }

        @keyframes slotIn { from { opacity: 0; transform: scaleY(.35); } to { opacity: 1; transform: scaleY(1); } }

        .mock__rooms { display: grid; gap: 8px; }
        .mock__room {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px;
            border: 1px solid rgba(233,229,219,.11);
            border-radius: 5px;
            background: rgba(255,255,255,.022);
            transition: border-color .3s, background-color .3s;
        }
        .mock__room:hover { border-color: rgba(192,138,46,.45); background: rgba(192,138,46,.05); }

        .mock__thumb {
            width: 52px; height: 40px;
            border-radius: 3px;
            flex: none;
            object-fit: cover;
            background: linear-gradient(135deg, #17334F, #0E1E30);
            border: 1px solid rgba(233,229,219,.1);
        }
        .mock__room-info { min-width: 0; flex: 1; }
        .mock__code {
            display: block;
            font-family: var(--f-mono);
            font-size: 10px;
            letter-spacing: .1em;
            color: var(--brass-soft);
        }
        .mock__room-name { display: block; font-size: 13px; font-weight: 500; color: #E2EAF2; }
        .mock__room-meta { display: block; font-size: 11px; color: #6E8299; }

        /* Kad terapung */
        .float {
            position: absolute;
            border-radius: 8px;
            border: 1px solid rgba(233,229,219,.16);
            background: rgba(14, 30, 48, .93);
            backdrop-filter: blur(10px);
            box-shadow: 0 26px 54px -24px rgba(0,0,0,.9);
            padding: 13px 15px;
            animation: bob 7s ease-in-out infinite;
        }

        .float--ticket { left: -46px; bottom: -46px; width: 208px; }

        /* Susunan mendatar supaya kad hanya menindih bar tajuk mockup */
        .float--qr {
            right: -34px; top: -36px; width: 190px;
            display: flex; align-items: center; gap: 12px;
            animation-delay: -3.5s;
        }
        .float--qr .qr { width: 54px; flex: none; }
        .float--qr .float__label { margin-bottom: 3px; }
        .float--qr .float__ref { margin-bottom: 0; font-size: 11.5px; }

        @keyframes bob {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-13px); }
        }

        .float__label {
            font-family: var(--f-mono);
            font-size: 9.5px;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: #6E8299;
            margin-bottom: 5px;
        }
        .float__ref {
            font-family: var(--f-mono);
            font-size: 12.5px;
            color: var(--on-ink);
            margin-bottom: 9px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 9px;
            border-radius: 999px;
            font-family: var(--f-mono);
            font-size: 10px;
            letter-spacing: .07em;
            text-transform: uppercase;
            border: 1px solid currentColor;
        }
        .chip::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .chip--active { color: #7FB6E3; }
        .chip--done   { color: #6FCB9B; }
        .chip--warn   { color: #E9A15C; }

        .float__sla { margin-top: 10px; }
        .float__sla-bar {
            height: 4px;
            border-radius: 2px;
            background: rgba(255,255,255,.1);
            overflow: hidden;
        }
        .float__sla-fill {
            height: 100%;
            width: 62%;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--st-done), #6FCB9B);
            animation: fill 2.4s cubic-bezier(.2,.8,.2,1) .6s backwards;
        }
        @keyframes fill { from { width: 0; } }
        .float__sla-text { font-family: var(--f-mono); font-size: 9.5px; color: #6E8299; margin-top: 5px; }

        .qr { width: 100%; height: auto; }
        .qr rect { fill: #E9E5DB; }

        /* ---------- Jalur statistik ---------- */

        .band {
            background: var(--ink-2);
            color: var(--on-ink);
            border-block: 1px solid rgba(233,229,219,.1);
            position: relative;
            overflow: hidden;
        }

        .band__grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }

        .band__cell {
            padding: clamp(32px, 4.5vw, 52px) clamp(18px, 2.6vw, 34px);
            border-right: 1px solid rgba(233,229,219,.1);
            position: relative;
        }
        .band__cell:last-child { border-right: 0; }

        .band__cell::before {
            content: attr(data-id);
            position: absolute;
            top: 14px; right: 16px;
            font-family: var(--f-mono);
            font-size: 10px;
            letter-spacing: .1em;
            color: rgba(150,166,184,.5);
        }

        .band__num {
            font-family: var(--f-display);
            font-size: clamp(40px, 5.2vw, 60px);
            font-weight: 600;
            letter-spacing: -.035em;
            line-height: 1;
            color: var(--brass-soft);
            display: flex;
            align-items: baseline;
            gap: 2px;
        }
        .band__num small { font-size: .48em; font-weight: 600; letter-spacing: 0; }

        .band__cap { margin-top: 12px; font-size: 14.5px; color: var(--on-ink-dim); max-width: 24ch; }

        /* ---------- Sebelum / Selepas ---------- */

        .compare {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: clamp(20px, 3vw, 40px);
            align-items: stretch;
        }

        .compare__col {
            border: 1px solid var(--line-paper);
            border-radius: 10px;
            padding: clamp(24px, 3vw, 36px);
            background: var(--paper-2);
            position: relative;
        }

        .compare__col--before { background: transparent; border-style: dashed; }

        .compare__col--after {
            border-color: rgba(192,138,46,.45);
            box-shadow: 0 24px 50px -34px rgba(16,30,46,.5);
        }

        .compare__tag {
            display: inline-block;
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .16em;
            text-transform: uppercase;
            padding: 5px 11px;
            border-radius: 4px;
            margin-bottom: 22px;
        }
        .compare__tag--before { background: var(--paper-3); color: var(--on-paper-dim); }
        .compare__tag--after { background: rgba(192,138,46,.15); color: var(--brass-ink); }

        .compare__list { display: grid; gap: 15px; }
        .compare__list li {
            display: flex;
            gap: 12px;
            font-size: 15px;
            line-height: 1.6;
        }
        .compare__list li svg { width: 18px; height: 18px; flex: none; margin-top: 3px; }
        .compare__col--before li { color: var(--on-paper-dim); }
        .compare__col--before li svg { color: var(--st-danger); opacity: .75; }
        .compare__col--after li svg { color: var(--st-done); }

        .compare__arrow {
            display: grid;
            place-items: center;
            color: var(--brass-ink);
        }
        .compare__arrow svg { width: 30px; height: 30px; animation: nudge 2.6s ease-in-out infinite; }
        @keyframes nudge { 0%,100% { transform: translateX(0); } 50% { transform: translateX(7px); } }

        /* ---------- Kad domain ---------- */

        .domains { display: grid; grid-template-columns: 1fr 1fr; gap: clamp(20px, 2.6vw, 32px); }

        .domain {
            border: 1px solid var(--line-paper);
            border-radius: 12px;
            background: var(--paper-2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .45s cubic-bezier(.2,.8,.2,1), box-shadow .45s, border-color .45s;
        }
        .domain:hover {
            transform: translateY(-5px);
            border-color: rgba(192,138,46,.45);
            box-shadow: 0 34px 60px -40px rgba(16,30,46,.6);
        }

        .domain__media {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: linear-gradient(140deg, var(--ink-3), var(--ink));
        }
        /* Ilustrasi lukisan teknikal menggantikan foto gambar letak */
        .illus { width: 100%; height: 100%; display: block; }
        .illus text { font-family: var(--f-mono); }

        .domain__media .illus {
            transition: transform .8s cubic-bezier(.2,.8,.2,1);
        }
        .domain:hover .domain__media .illus { transform: scale(1.04); }

        .domain__media::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(10,21,34,.92) 4%, rgba(10,21,34,.15) 55%, transparent 80%);
        }

        .domain__stamp {
            position: absolute;
            z-index: 2;
            left: 18px; top: 18px;
            display: flex; align-items: center; gap: 9px;
            padding: 7px 12px;
            border-radius: 5px;
            background: rgba(10,21,34,.72);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(233,229,219,.2);
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: var(--brass-soft);
        }
        .domain__stamp svg { width: 15px; height: 15px; }

        .domain__title {
            position: absolute;
            z-index: 2;
            left: 22px; right: 22px; bottom: 18px;
            color: var(--on-ink);
            font-family: var(--f-display);
            font-size: clamp(22px, 2.2vw, 28px);
            font-weight: 600;
            letter-spacing: -.02em;
        }

        .domain__body { padding: clamp(22px, 2.6vw, 30px); display: flex; flex-direction: column; flex: 1; }
        .domain__body > p { color: var(--on-paper-dim); font-size: 15.5px; margin-bottom: 22px; }

        .domain__feats { display: grid; gap: 11px; margin-bottom: 26px; }
        .domain__feats li { display: flex; gap: 11px; font-size: 14.5px; align-items: flex-start; }
        .domain__feats svg { width: 17px; height: 17px; color: var(--brass-ink); flex: none; margin-top: 4px; }

        .domain__foot {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--line-paper);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .domain__mods {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--on-paper-dim);
        }

        .link-arrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14.5px;
            font-weight: 600;
            color: var(--brass-ink);
        }
        .link-arrow svg { width: 16px; height: 16px; transition: transform .3s cubic-bezier(.2,.8,.2,1); }
        .link-arrow:hover svg { transform: translateX(4px); }

        /* ---------- Marquee modul ---------- */

        .marquee {
            border-block: 1px solid var(--line-paper);
            background: var(--paper-3);
            overflow: hidden;
            padding-block: 15px;
            position: relative;
        }
        .marquee::before, .marquee::after {
            content: '';
            position: absolute; top: 0; bottom: 0;
            width: 110px; z-index: 2;
            pointer-events: none;
        }
        .marquee::before { left: 0; background: linear-gradient(to right, var(--paper-3), transparent); }
        .marquee::after { right: 0; background: linear-gradient(to left, var(--paper-3), transparent); }

        .marquee__track {
            display: flex;
            gap: 44px;
            width: max-content;
            animation: slide 42s linear infinite;
        }
        .marquee:hover .marquee__track { animation-play-state: paused; }

        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        .marquee__item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--f-mono);
            font-size: 12px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--on-paper-dim);
            white-space: nowrap;
        }
        .marquee__item::before {
            content: '';
            width: 5px; height: 5px;
            transform: rotate(45deg);
            background: var(--brass);
            flex: none;
        }

        /* ---------- Grid modul ---------- */

        .layer { margin-bottom: clamp(34px, 4vw, 52px); }
        .layer:last-child { margin-bottom: 0; }

        .layer__head {
            display: flex;
            align-items: baseline;
            gap: 14px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line-ink);
        }
        .layer__name { font-family: var(--f-display); font-size: 20px; font-weight: 600; }
        .layer__count {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--on-ink-dim);
            margin-left: auto;
        }

        /* Lajur ditetapkan per lapisan supaya tiada baris tergantung separuh kosong */
        .modules { display: grid; grid-template-columns: repeat(var(--cols, 4), 1fr); gap: 14px; }

        .module {
            position: relative;
            padding: 20px 18px 18px;
            border: 1px solid var(--line-ink);
            border-radius: 8px;
            background: rgba(255,255,255,.022);
            overflow: hidden;
            transition: transform .4s cubic-bezier(.2,.8,.2,1), border-color .4s, background-color .4s;
        }
        .module:hover {
            transform: translateY(-4px);
            border-color: rgba(192,138,46,.5);
            background: rgba(192,138,46,.06);
        }

        /* Tanda sudut lukisan teknikal */
        .module::before, .module::after {
            content: '';
            position: absolute;
            width: 9px; height: 9px;
            border-color: var(--brass);
            opacity: 0;
            transition: opacity .4s;
        }
        .module::before { top: 7px; left: 7px; border-top: 1px solid; border-left: 1px solid; }
        .module::after { bottom: 7px; right: 7px; border-bottom: 1px solid; border-right: 1px solid; }
        .module:hover::before, .module:hover::after { opacity: 1; }

        .module__id {
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .13em;
            color: var(--brass-soft);
            margin-bottom: 12px;
        }
        .module__icon {
            width: 34px; height: 34px;
            display: grid; place-items: center;
            border-radius: 6px;
            background: rgba(233,229,219,.06);
            border: 1px solid var(--line-ink);
            margin-bottom: 13px;
            transition: background-color .4s, border-color .4s, transform .5s cubic-bezier(.2,.8,.2,1);
        }
        .module__icon svg { width: 18px; height: 18px; color: var(--on-ink); }
        .module:hover .module__icon {
            background: rgba(192,138,46,.16);
            border-color: rgba(192,138,46,.5);
            transform: rotate(-6deg) scale(1.06);
        }
        .module__name { font-size: 14.5px; font-weight: 600; line-height: 1.35; margin-bottom: 6px; }
        .module__desc { font-size: 13px; line-height: 1.55; color: var(--on-ink-dim); }

        /* ---------- Aliran kerja (tab) ---------- */

        .tabs {
            display: inline-flex;
            padding: 5px;
            gap: 4px;
            border: 1px solid var(--line-paper);
            border-radius: 8px;
            background: var(--paper-2);
            margin-bottom: clamp(34px, 4vw, 48px);
        }
        .tab {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14.5px;
            font-weight: 600;
            color: var(--on-paper-dim);
            transition: background-color .3s, color .3s;
        }
        .tab[aria-selected="true"] { background: var(--ink); color: var(--on-ink); }
        .tab:not([aria-selected="true"]):hover { color: var(--on-paper); background: var(--paper-3); }

        .flow[hidden] { display: none; }

        .steps { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0; }

        .step {
            position: relative;
            padding: 0 20px 0 0;
            animation: stepIn .55s cubic-bezier(.2,.8,.2,1) backwards;
        }
        @keyframes stepIn { from { opacity: 0; transform: translateY(16px); } }

        .step__rail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .step__num {
            width: 34px; height: 34px;
            flex: none;
            display: grid; place-items: center;
            border-radius: 50%;
            border: 1px solid var(--brass);
            background: var(--paper-2);
            font-family: var(--f-mono);
            font-size: 12px;
            color: var(--brass-ink);
            position: relative;
            z-index: 2;
        }
        .step__line { height: 1px; flex: 1; background: var(--line-paper); position: relative; overflow: hidden; }
        .step__line::after {
            content: '';
            position: absolute; inset: 0;
            background: var(--brass);
            transform: scaleX(0); transform-origin: left;
            animation: railGrow .8s cubic-bezier(.2,.8,.2,1) forwards;
            animation-delay: inherit;
        }
        @keyframes railGrow { to { transform: scaleX(1); } }
        .step:last-child .step__line { display: none; }

        .step__title { font-size: 15.5px; font-weight: 600; margin-bottom: 7px; font-family: var(--f-body); letter-spacing: -.005em; }
        .step__desc { font-size: 13.5px; line-height: 1.6; color: var(--on-paper-dim); }
        .step__meta {
            margin-top: 10px;
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--brass-ink);
        }

        /* ---------- Bento ciri ---------- */

        .bento {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
        }

        .tile {
            position: relative;
            border: 1px solid var(--line-paper);
            border-radius: 10px;
            background: var(--paper-2);
            padding: clamp(22px, 2.4vw, 30px);
            overflow: hidden;
            transition: transform .45s cubic-bezier(.2,.8,.2,1), border-color .45s, box-shadow .45s;
        }
        .tile:hover {
            transform: translateY(-4px);
            border-color: rgba(192,138,46,.45);
            box-shadow: 0 28px 52px -38px rgba(16,30,46,.55);
        }

        .tile--wide { grid-column: span 3; }
        .tile--third { grid-column: span 2; }
        .tile--half { grid-column: span 3; }

        .tile--dark {
            background: var(--ink);
            border-color: rgba(233,229,219,.16);
            color: var(--on-ink);
        }
        .tile--dark .tile__desc { color: var(--on-ink-dim); }

        .tile__icon {
            width: 40px; height: 40px;
            display: grid; place-items: center;
            border-radius: 8px;
            background: rgba(192,138,46,.13);
            border: 1px solid rgba(192,138,46,.32);
            color: var(--brass-ink);
            margin-bottom: 18px;
            transition: transform .5s cubic-bezier(.2,.8,.2,1);
        }
        .tile--dark .tile__icon { color: var(--brass-soft); background: rgba(192,138,46,.16); }
        .tile__icon svg { width: 20px; height: 20px; }
        .tile:hover .tile__icon { transform: translateY(-3px) rotate(-5deg); }

        .tile__title { font-size: 18px; font-weight: 600; margin-bottom: 9px; }
        .tile__desc { font-size: 14.5px; line-height: 1.6; color: var(--on-paper-dim); }

        .tile__demo { margin-top: 20px; }

        /* Demo: label QR aset */
        .tag {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px;
            border: 1px dashed var(--line-paper);
            border-radius: 7px;
            background: var(--paper);
        }
        .tile--dark .tag { background: rgba(255,255,255,.03); border-color: rgba(233,229,219,.2); }
        .tag__qr { width: 54px; height: 54px; flex: none; }
        .tag__qr rect { fill: var(--on-paper); }
        .tile--dark .tag__qr rect { fill: var(--on-ink); }
        .tag__meta { min-width: 0; }
        .tag__code { display: block; font-family: var(--f-mono); font-size: 13px; letter-spacing: .06em; }
        .tag__desc { display: block; font-size: 12px; color: var(--on-paper-dim); }
        .tile--dark .tag__desc { color: var(--on-ink-dim); }

        /* Demo: bar SLA */
        .sla { display: grid; gap: 11px; }
        .sla__row { display: grid; grid-template-columns: 84px 1fr 44px; align-items: center; gap: 11px; }
        .sla__pri {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .06em;
            white-space: nowrap;
            color: var(--on-paper-dim);
        }
        .tile--dark .sla__pri { color: var(--on-ink-dim); }
        .sla__track { height: 6px; border-radius: 3px; background: rgba(16,30,46,.1); overflow: hidden; }
        .tile--dark .sla__track { background: rgba(255,255,255,.1); }
        .sla__fill { display: block; height: 100%; border-radius: 3px; transform-origin: left; transform: scaleX(0); transition: transform 1.1s cubic-bezier(.2,.8,.2,1); }
        .is-in .sla__fill { transform: scaleX(1); }
        .sla__val { font-family: var(--f-mono); font-size: 11.5px; text-align: right; }

        /* Demo: senarai notifikasi */
        .notif { display: grid; gap: 8px; }
        .notif li {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 11px;
            border: 1px solid var(--line-paper);
            border-radius: 5px;
            font-size: 12.5px;
            background: var(--paper);
        }
        .tile--dark .notif li { background: rgba(255,255,255,.03); border-color: rgba(233,229,219,.14); }
        .notif svg { width: 15px; height: 15px; flex: none; color: var(--brass-ink); }
        .tile--dark .notif svg { color: var(--brass-soft); }
        .notif code { font-family: var(--f-mono); font-size: 11.5px; color: var(--brass-ink); }
        .tile--dark .notif code { color: var(--brass-soft); }

        /* ---------- Peranan ---------- */

        .roles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

        .role {
            border: 1px solid var(--line-paper);
            border-radius: 10px;
            background: var(--paper-2);
            overflow: hidden;
            transition: transform .45s cubic-bezier(.2,.8,.2,1), border-color .45s, box-shadow .45s;
        }
        .role:hover {
            transform: translateY(-4px);
            border-color: rgba(192,138,46,.45);
            box-shadow: 0 26px 48px -36px rgba(16,30,46,.5);
        }

        .role__media {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: linear-gradient(140deg, var(--paper-3), var(--paper));
        }
        .role__media .illus { transition: transform .7s cubic-bezier(.2,.8,.2,1); }
        .role:hover .role__media .illus { transform: scale(1.05); }

        .role__id {
            position: absolute;
            top: 11px; left: 11px;
            padding: 4px 9px;
            border-radius: 4px;
            background: rgba(10,21,34,.78);
            backdrop-filter: blur(6px);
            font-family: var(--f-mono);
            font-size: 10px;
            letter-spacing: .12em;
            color: var(--brass-soft);
        }

        .role__body { padding: 18px; }
        .role__name { font-size: 15.5px; font-weight: 600; font-family: var(--f-body); margin-bottom: 3px; }
        .role__who {
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--brass-ink);
            margin-bottom: 11px;
        }
        .role__need { font-size: 13.5px; line-height: 1.55; color: var(--on-paper-dim); }

        /* ---------- Fasa ---------- */

        .phases { display: grid; gap: 0; }

        .phase {
            display: grid;
            grid-template-columns: 132px 40px 1fr 250px;
            gap: clamp(16px, 2.4vw, 34px);
            align-items: start;
            padding-block: clamp(24px, 3vw, 34px);
            border-top: 1px solid var(--line-ink);
            transition: background-color .4s;
        }
        .phase:last-child { border-bottom: 1px solid var(--line-ink); }
        .phase:hover { background: rgba(255,255,255,.028); }

        .phase__id {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--brass-soft);
            padding-top: 5px;
        }
        .phase__dur { display: block; color: var(--on-ink-dim); margin-top: 6px; letter-spacing: .08em; }

        .phase__mark {
            display: grid;
            place-items: center;
            padding-top: 3px;
        }
        .phase__bullet {
            width: 13px; height: 13px;
            border-radius: 50%;
            border: 1px solid var(--brass);
            background: var(--ink);
            position: relative;
        }
        .phase__bullet::after {
            content: '';
            position: absolute; inset: 3px;
            border-radius: 50%;
            background: var(--brass);
            opacity: 0;
            transition: opacity .35s;
        }
        .phase:hover .phase__bullet::after { opacity: 1; }

        .phase__title { font-size: clamp(19px, 2vw, 24px); margin-bottom: 9px; }
        .phase__desc { font-size: 15px; color: var(--on-ink-dim); max-width: 56ch; }

        .phase__tags { display: flex; flex-wrap: wrap; gap: 6px; padding-top: 4px; }
        .phase__tags span {
            font-family: var(--f-mono);
            font-size: 10.5px;
            letter-spacing: .09em;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid var(--line-ink);
            color: var(--on-ink-dim);
        }

        /* ---------- Keselamatan ---------- */

        .secure { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

        .secure__item {
            padding: 26px 22px;
            border: 1px solid var(--line-paper);
            border-radius: 10px;
            background: var(--paper-2);
            transition: transform .4s cubic-bezier(.2,.8,.2,1), border-color .4s;
        }
        .secure__item:hover { transform: translateY(-4px); border-color: rgba(44,110,155,.45); }

        .secure__item svg { width: 22px; height: 22px; color: var(--steel); margin-bottom: 16px; }
        .secure__item h3 { font-size: 16px; font-weight: 600; font-family: var(--f-body); margin-bottom: 8px; }
        .secure__item p { font-size: 13.5px; line-height: 1.6; color: var(--on-paper-dim); }

        /* ---------- CTA ---------- */

        .cta {
            position: relative;
            overflow: hidden;
            background: var(--ink);
            color: var(--on-ink);
            text-align: center;
        }
        .cta__inner { position: relative; z-index: 2; max-width: 62ch; margin-inline: auto; }
        .cta .lead { margin: 20px auto 34px; }
        .cta__actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .cta__note {
            margin-top: 26px;
            font-family: var(--f-mono);
            font-size: 11.5px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--on-ink-dim);
        }

        .cta__ring {
            position: absolute;
            width: 640px; height: 640px;
            border-radius: 50%;
            border: 1px solid rgba(192,138,46,.16);
            left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .cta__ring--2 { width: 900px; height: 900px; border-color: rgba(192,138,46,.09); }
        .cta__ring--3 { width: 1180px; height: 1180px; border-color: rgba(192,138,46,.05); }

        /* ---------- Footer ---------- */

        .foot {
            background: #071019;
            color: var(--on-ink-dim);
            padding-block: clamp(48px, 6vw, 72px) 34px;
            border-top: 1px solid var(--line-ink);
        }

        .foot__grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr 1fr;
            gap: clamp(24px, 4vw, 56px);
            padding-bottom: 40px;
            border-bottom: 1px solid var(--line-ink);
        }

        .foot__about { font-size: 14px; line-height: 1.7; max-width: 40ch; margin-top: 18px; }

        .foot h4 {
            font-family: var(--f-mono);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--on-ink);
            margin-bottom: 16px;
        }
        .foot__links { display: grid; gap: 10px; }
        .foot__links a { font-size: 14px; transition: color .25s; }
        .foot__links a:hover { color: var(--brass-soft); }

        .foot__bottom {
            padding-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: space-between;
            align-items: center;
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: .07em;
        }
        .foot__bottom span { color: rgba(150,166,184,.75); }

        /* ---------- Sistem pendedahan skrol ---------- */

        .js [data-reveal] {
            opacity: 0;
            transform: translateY(26px);
            transition:
                opacity .8s cubic-bezier(.2,.8,.2,1) var(--d, 0s),
                transform .8s cubic-bezier(.2,.8,.2,1) var(--d, 0s);
        }
        .js [data-reveal].is-in { opacity: 1; transform: none; }

        /* Muat naik hero berperingkat */
        .js .hero [data-rise] {
            opacity: 0;
            animation: rise .9s cubic-bezier(.2,.8,.2,1) forwards;
            animation-delay: var(--d, 0s);
        }
        @keyframes rise { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }

        /* ---------- Responsif ---------- */

        @media (max-width: 1080px) {
            .modules { grid-template-columns: repeat(3, 1fr); }
            .roles { grid-template-columns: repeat(3, 1fr); }
            .band__grid { grid-template-columns: repeat(2, 1fr); }
            .band__cell:nth-child(2) { border-right: 0; }
            .band__cell:nth-child(-n+2) { border-bottom: 1px solid rgba(233,229,219,.1); }
            .steps { grid-template-columns: repeat(2, 1fr); gap: 30px 20px; }
            .step { padding-right: 0; }
            .step__line { display: none; }
            .phase { grid-template-columns: 110px 28px 1fr; }
            .phase__tags { grid-column: 3; }
        }

        /* Navigasi bertukar kepada menu lipat sebelum pautan sempat membalut */
        @media (max-width: 1100px) {
            .nav__links { display: none; }
            .nav__cta .btn--ghost-ink { display: none; }
            .nav__burger { display: grid; }
            /* Pautan yang disembunyikan tidak lagi menolak, jadi jajarkan di sini */
            .nav__cta { margin-left: auto; }
        }

        @media (max-width: 900px) {
            .hero__grid { grid-template-columns: 1fr; }
            .mock { margin-top: 20px; }
            .mock__app { transform: none; }
            .mock:hover .mock__app { transform: none; }
            .float--ticket { left: -14px; bottom: -34px; width: 200px; }
            .float--qr { right: -10px; top: -30px; width: 176px; }
            .domains { grid-template-columns: 1fr; }
            .compare { grid-template-columns: 1fr; }
            .compare__arrow { transform: rotate(90deg); padding-block: 4px; }
            .bento { grid-template-columns: repeat(2, 1fr); }
            .tile--wide, .tile--half, .tile--third { grid-column: span 2; }
            .secure { grid-template-columns: repeat(2, 1fr); }
            .foot__grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 680px) {
            body { font-size: 16px; }
            /* Ruang bar navigasi hanya cukup untuk jenama dan butang menu */
            .nav__cta .btn--primary { display: none; }
            .modules { grid-template-columns: repeat(2, 1fr); }
            .roles { grid-template-columns: repeat(2, 1fr); }
            .steps { grid-template-columns: 1fr; }
            .hero__facts li { padding-right: 20px; margin-right: 20px; }
            .hero__facts b { font-size: 25px; }
            .mock__filters { grid-template-columns: 1fr 1fr; }
            .mock__filters .mock__field:last-child { display: none; }
            /* Skrin sempit: kad disusun di bawah mockup, bukan bertindih */
            .float--ticket { position: static; width: auto; margin-top: 14px; animation: none; }
            .float--qr { position: static; width: auto; margin-top: 10px; animation: none; }
            .phase { grid-template-columns: 1fr; gap: 12px; }
            .phase__mark { display: none; }
            .bento, .secure, .foot__grid { grid-template-columns: 1fr; }
            .tile--wide, .tile--half, .tile--third { grid-column: span 1; }
            .band__grid { grid-template-columns: 1fr; }
            .band__cell { border-right: 0; border-bottom: 1px solid rgba(233,229,219,.1); }
            .band__cell:last-child { border-bottom: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
            .js [data-reveal] { opacity: 1; transform: none; }
            .js .hero [data-rise] { opacity: 1; }
        }
    </style>
</head>

<body>
@php
    /* ---------------------------------------------------------------
     | Kandungan halaman — diambil daripada docs-claude/
     |   00-project-charter.md · 01-modules.md · 03-URS · 10-ui-ux-spec
     --------------------------------------------------------------- */

    $icons = [
        'calendar'  => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
        'wrench'    => '<path d="M20.5 7.5a4.5 4.5 0 0 1-5.9 4.3L7 19.4a2.3 2.3 0 0 1-3.3-3.3l7.6-7.6A4.5 4.5 0 0 1 17.8 3l-3 3 2.2 2.2 3-3c.3.7.5 1.5.5 2.3z"/>',
        'qr'        => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20.5 14v1M14 20.5h1M18 18h3v3h-3z"/>',
        'shield'    => '<path d="M12 3l7.5 3v5.5c0 4.6-3.1 8.4-7.5 9.8-4.4-1.4-7.5-5.2-7.5-9.8V6z"/><path d="M9 12l2 2 4-4"/>',
        'bell'      => '<path d="M18 8.5a6 6 0 1 0-12 0c0 6-2.5 7.5-2.5 7.5h17S18 14.5 18 8.5z"/><path d="M13.7 20a2 2 0 0 1-3.4 0"/>',
        'chart'     => '<path d="M3 21h18"/><rect x="5" y="12" width="3.4" height="6" rx="1"/><rect x="10.3" y="8" width="3.4" height="10" rx="1"/><rect x="15.6" y="4" width="3.4" height="14" rx="1"/>',
        'users'     => '<circle cx="9" cy="8" r="3.2"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0"/><path d="M16 5.3a3.2 3.2 0 0 1 0 5.6M17.5 14.4A6.2 6.2 0 0 1 21.2 20"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v2.6M12 18.9v2.6M21.5 12h-2.6M5.1 12H2.5M18.7 5.3l-1.8 1.8M7.1 16.9l-1.8 1.8M18.7 18.7l-1.8-1.8M7.1 7.1L5.3 5.3"/>',
        'pin'       => '<path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
        'box'       => '<path d="M12 2.8l8.5 4.6v9.2L12 21.2 3.5 16.6V7.4z"/><path d="M3.5 7.4L12 12l8.5-4.6M12 12v9.2"/>',
        'clipboard' => '<rect x="5" y="4.5" width="14" height="16.5" rx="2"/><path d="M9 4.5V3.4A1.4 1.4 0 0 1 10.4 2h3.2A1.4 1.4 0 0 1 15 3.4v1.1z"/><path d="M9 13l2 2 4-4"/>',
        'truck'     => '<path d="M2.5 6.5h11v10h-11z"/><path d="M13.5 10h4l3 3v3.5h-7z"/><circle cx="7" cy="18.5" r="1.9"/><circle cx="17" cy="18.5" r="1.9"/>',
        'layers'    => '<path d="M12 3l9 4.5-9 4.5-9-4.5z"/><path d="M3 12l9 4.5 9-4.5M3 16.5L12 21l9-4.5"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 6.8V12l3.4 2"/>',
        'lock'      => '<rect x="4.5" y="10" width="15" height="10.5" rx="2"/><path d="M8 10V7.5a4 4 0 1 1 8 0V10"/>',
        'database'  => '<ellipse cx="12" cy="6" rx="8" ry="3.2"/><path d="M4 6v12c0 1.8 3.6 3.2 8 3.2s8-1.4 8-3.2V6"/><path d="M4 12c0 1.8 3.6 3.2 8 3.2s8-1.4 8-3.2"/>',
        'workflow'  => '<rect x="3" y="3.5" width="7" height="6" rx="1.5"/><rect x="14" y="14.5" width="7" height="6" rx="1.5"/><path d="M6.5 9.5v5a3 3 0 0 0 3 3H14"/>',
        'monitor'   => '<rect x="3" y="4" width="18" height="12.5" rx="2"/><path d="M8.5 20.5h7M12 16.5v4"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="M16.2 16.2L21 21"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check'     => '<path d="M4.8 12.6l4.7 4.7L19.2 6.8"/>',
        'cross'     => '<path d="M6 6l12 12M18 6L6 18"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.4 6.3L12 12.6l8.6-6.3"/>',
    ];

    $icon = function (string $name, array $attr = []) use ($icons) {
        $extra = '';
        foreach ($attr as $k => $v) {
            $extra .= ' ' . $k . '="' . e($v) . '"';
        }
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" '
             . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"' . $extra . '>'
             . ($icons[$name] ?? '') . '</svg>';
    };

    /* Corak QR hiasan — deterministik, bukan kod boleh imbas */
    $qr = function (string $class = 'qr') {
        $cells = [
            [8,0],[10,0],[12,0],[13,0],[16,0],[9,1],[11,1],[14,1],[8,2],[12,2],[13,2],[15,2],[16,2],
            [9,3],[10,3],[14,3],[8,4],[11,4],[13,4],[16,4],[10,5],[12,5],[15,5],[8,6],[9,6],[13,6],[14,6],[16,6],
            [0,8],[2,8],[4,8],[6,8],[9,8],[11,8],[13,8],[16,8],[18,8],[20,8],
            [1,9],[3,9],[8,9],[10,9],[14,9],[17,9],[19,9],
            [0,10],[5,10],[9,10],[12,10],[15,10],[18,10],[20,10],
            [2,11],[4,11],[8,11],[11,11],[13,11],[16,11],[19,11],
            [1,12],[3,12],[6,12],[10,12],[12,12],[14,12],[17,12],[20,12],
            [0,13],[4,13],[9,13],[11,13],[15,13],[18,13],
            [2,14],[5,14],[8,14],[13,14],[16,14],[19,14],[20,14],
            [8,15],[10,15],[14,15],[17,15],[9,16],[12,16],[16,16],[18,16],[20,16],
            [8,17],[11,17],[13,17],[15,17],[19,17],[10,18],[14,18],[17,18],[20,18],
            [9,19],[12,19],[16,19],[18,19],[8,20],[11,20],[15,20],[19,20],
        ];
        $out = '<svg class="' . e($class) . '" viewBox="0 0 21 21" aria-hidden="true">';
        /* Tiga corak pengecam sudut */
        foreach ([[0,0],[14,0],[0,14]] as [$fx, $fy]) {
            $out .= '<rect x="' . $fx . '" y="' . $fy . '" width="7" height="1"/>'
                  . '<rect x="' . $fx . '" y="' . ($fy + 6) . '" width="7" height="1"/>'
                  . '<rect x="' . $fx . '" y="' . ($fy + 1) . '" width="1" height="5"/>'
                  . '<rect x="' . ($fx + 6) . '" y="' . ($fy + 1) . '" width="1" height="5"/>'
                  . '<rect x="' . ($fx + 2) . '" y="' . ($fy + 2) . '" width="3" height="3"/>';
        }
        foreach ($cells as [$cx, $cy]) {
            $out .= '<rect x="' . $cx . '" y="' . $cy . '" width="1" height="1"/>';
        }
        return $out . '</svg>';
    };

    /* ------------------------------------------------------------------
     | Ilustrasi gambar letak — dilukis sendiri dalam gaya lukisan teknikal
     | supaya kekal sesuai dengan domain sistem dan tidak bergantung pada
     | sebarang perkhidmatan imej luaran.
     ------------------------------------------------------------------ */

    $gridDefs = function (string $id, string $stroke, int $step = 16) {
        return '<defs><pattern id="' . $id . '" width="' . $step . '" height="' . $step . '" '
             . 'patternUnits="userSpaceOnUse">'
             . '<path d="M' . $step . ' 0H0V' . $step . '" fill="none" stroke="' . $stroke . '" stroke-width="1"/>'
             . '</pattern></defs>';
    };

    /* Tanda sudut gaya lukisan kejuruteraan */
    $ticks = function (float $x, float $y, float $w, float $h, string $c, float $len = 12) {
        $t = '';
        $corners = [
            [$x, $y, $len, $len],
            [$x + $w, $y, -$len, $len],
            [$x, $y + $h, $len, -$len],
            [$x + $w, $y + $h, -$len, -$len],
        ];
        foreach ($corners as [$cx, $cy, $dx, $dy]) {
            $t .= '<path d="M' . $cx . ' ' . ($cy + $dy) . 'V' . $cy . 'H' . ($cx + $dx) . '" '
                . 'fill="none" stroke="' . $c . '" stroke-width="1.2"/>';
        }
        return $t;
    };

    /* Domain A — pelan lantai bilik mesyuarat */
    $illusRoom = function () use ($gridDefs, $ticks) {
        $line  = 'rgba(233,229,219,.55)';
        $brass = '#C08A2E';
        $dim   = 'rgba(150,166,184,.75)';

        $s = '<svg class="illus" viewBox="0 0 480 270" role="img" '
           . 'aria-label="Ilustrasi gambar letak: pelan lantai bilik mesyuarat BM-A-301 dengan meja, kerusi dan skrin paparan">';
        $s .= $gridDefs('gr-room', 'rgba(233,229,219,.06)');
        $s .= '<rect width="480" height="270" fill="#0E1E30"/>';
        $s .= '<rect width="480" height="270" fill="url(#gr-room)"/>';

        /* Dinding bilik, dengan bukaan pintu di dinding bawah */
        $s .= '<path d="M96 184V58h288v126H166M126 184H96" fill="none" stroke="' . $line . '" stroke-width="2.4"/>';
        /* Daun pintu dan ayunannya */
        $s .= '<path d="M126 144v40" fill="none" stroke="' . $line . '" stroke-width="1.6"/>';
        $s .= '<path d="M126 144a40 40 0 0 1 40 40" fill="none" stroke="' . $line . '" stroke-width="1" stroke-dasharray="4 3" opacity=".75"/>';

        /* Skrin paparan pada dinding atas */
        $s .= '<path d="M204 58h72" stroke="' . $brass . '" stroke-width="5" stroke-linecap="round"/>';

        /* Meja mesyuarat */
        $s .= '<rect x="176" y="94" width="128" height="54" rx="8" fill="rgba(192,138,46,.12)" stroke="' . $brass . '" stroke-width="1.6"/>';

        /* Kerusi mengelilingi meja */
        foreach ([186, 222, 258] as $cx) {
            $s .= '<rect x="' . $cx . '" y="78" width="24" height="10" rx="3" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';
            $s .= '<rect x="' . $cx . '" y="154" width="24" height="10" rx="3" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';
        }
        foreach ([100, 124] as $cy) {
            $s .= '<rect x="154" y="' . $cy . '" width="10" height="24" rx="3" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';
            $s .= '<rect x="316" y="' . $cy . '" width="10" height="24" rx="3" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';
        }

        /* Garis dimensi, dijaga di atas kawasan tajuk kad */
        $s .= '<path d="M96 198h288M96 192v12M384 192v12" fill="none" stroke="' . $dim . '" stroke-width="1"/>';
        $s .= '<rect x="214" y="189" width="52" height="18" fill="#0E1E30"/>';
        $s .= '<text x="240" y="202" fill="' . $dim . '" font-size="10" text-anchor="middle">12.0 m</text>';

        /* Label diletak di kanan atas supaya tidak dilindungi lencana domain */
        $s .= '<text x="384" y="40" fill="' . $brass . '" font-size="11" letter-spacing="1.5" text-anchor="end">BM-A-301 · KAPASITI 20</text>';

        $s .= $ticks(96, 58, 288, 126, 'rgba(192,138,46,.55)');
        return $s . '</svg>';
    };

    /* Domain B — skema peralatan ICT berlabel QR */
    $illusAsset = function () use ($gridDefs, $ticks) {
        $line  = 'rgba(233,229,219,.55)';
        $brass = '#C08A2E';
        $dim   = 'rgba(150,166,184,.75)';

        /* Petak QR ringkas sebagai penanda label aset */
        $tag = function (float $x, float $y) use ($brass) {
            $t = '<rect x="' . $x . '" y="' . $y . '" width="18" height="18" rx="2" fill="rgba(192,138,46,.16)" stroke="' . $brass . '" stroke-width="1"/>';
            foreach ([[3,3],[11,3],[3,11]] as [$dx, $dy]) {
                $t .= '<rect x="' . ($x + $dx) . '" y="' . ($y + $dy) . '" width="4" height="4" fill="' . $brass . '"/>';
            }
            $t .= '<rect x="' . ($x + 12) . '" y="' . ($y + 12) . '" width="2" height="2" fill="' . $brass . '"/>';
            return $t;
        };

        $s = '<svg class="illus" viewBox="0 0 480 270" role="img" '
           . 'aria-label="Ilustrasi gambar letak: skema peralatan ICT berdaftar — komputer, pencetak dan penghala, setiap satu berlabel kod QR">';
        $s .= $gridDefs('gr-asset', 'rgba(233,229,219,.06)');
        $s .= '<rect width="480" height="270" fill="#0E1E30"/>';
        $s .= '<rect width="480" height="270" fill="url(#gr-asset)"/>';

        /* Monitor dan kaki */
        $s .= '<rect x="52" y="60" width="112" height="72" rx="5" fill="none" stroke="' . $line . '" stroke-width="1.8"/>';
        $s .= '<rect x="61" y="69" width="94" height="54" rx="2" fill="rgba(44,110,155,.22)"/>';
        $s .= '<path d="M108 132v12M86 144h44" fill="none" stroke="' . $line . '" stroke-width="1.8" stroke-linecap="round"/>';

        /* Unit sistem */
        $s .= '<rect x="184" y="60" width="40" height="96" rx="4" fill="none" stroke="' . $line . '" stroke-width="1.8"/>';
        $s .= '<path d="M193 73h22M193 83h22" fill="none" stroke="' . $line . '" stroke-width="1.4"/>';
        $s .= '<circle cx="204" cy="142" r="4" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';

        /* Pencetak */
        $s .= '<rect x="252" y="84" width="106" height="54" rx="5" fill="none" stroke="' . $line . '" stroke-width="1.8"/>';
        $s .= '<path d="M271 84V70h68v14" fill="none" stroke="' . $line . '" stroke-width="1.4"/>';
        $s .= '<rect x="275" y="106" width="60" height="15" rx="2" fill="rgba(233,229,219,.12)"/>';

        /* Penghala rangkaian */
        $s .= '<rect x="386" y="86" width="58" height="48" rx="5" fill="none" stroke="' . $line . '" stroke-width="1.8"/>';
        foreach ([395, 407, 419, 431] as $px) {
            $s .= '<rect x="' . $px . '" y="115" width="8" height="8" rx="1" fill="rgba(44,110,155,.55)"/>';
        }
        $s .= '<path d="M405 86V74M425 86V74" fill="none" stroke="' . $line . '" stroke-width="1.4"/>';

        /* Bas pendaftaran aset */
        $s .= '<path d="M62 182h370" fill="none" stroke="' . $brass . '" stroke-width="1.4" stroke-dasharray="6 4"/>';
        foreach ([108, 204, 305, 415] as $nx) {
            $s .= '<path d="M' . $nx . ' 156v26" fill="none" stroke="rgba(192,138,46,.5)" stroke-width="1"/>';
            $s .= '<circle cx="' . $nx . '" cy="182" r="3.4" fill="#0E1E30" stroke="' . $brass . '" stroke-width="1.4"/>';
        }

        /* Label QR pada setiap aset */
        $s .= $tag(146, 42) . $tag(206, 42) . $tag(340, 66) . $tag(426, 68);

        /* Teks, dijaga di luar kawasan lencana dan tajuk kad */
        $s .= '<text x="444" y="40" fill="' . $brass . '" font-size="11" letter-spacing="1.5" text-anchor="end">DAFTAR ASET ICT</text>';
        $s .= '<text x="62" y="202" fill="' . $dim . '" font-size="10" letter-spacing="1">AST-PC-004821</text>';
        $s .= '<text x="432" y="202" fill="' . $dim . '" font-size="10" letter-spacing="1" text-anchor="end">AST-PR-001172</text>';

        $s .= $ticks(52, 42, 392, 160, 'rgba(192,138,46,.45)');
        return $s . '</svg>';
    };

    /* Persona — bingkai potret gambar letak */
    $illusPerson = function (string $id, int $i) use ($gridDefs) {
        $ink   = 'rgba(16,30,46,.42)';
        $brass = 'rgba(192,138,46,.7)';

        $s = '<svg class="illus illus--person" viewBox="0 0 120 90" role="img" '
           . 'aria-label="Gambar letak potret bagi persona ' . e($id) . '">';
        $s .= $gridDefs('gr-p' . $i, 'rgba(16,30,46,.06)', 10);
        $s .= '<rect width="120" height="90" fill="#EDE9E0"/>';
        $s .= '<rect width="120" height="90" fill="url(#gr-p' . $i . ')"/>';

        /* Gelang aksen di belakang kepala */
        $s .= '<circle cx="60" cy="36" r="21" fill="none" stroke="' . $brass . '" stroke-width="1" stroke-dasharray="3 4"/>';
        /* Kepala dan bahu */
        $s .= '<circle cx="60" cy="36" r="13" fill="none" stroke="' . $ink . '" stroke-width="1.6"/>';
        $s .= '<path d="M38 78a22 22 0 0 1 44 0" fill="none" stroke="' . $ink . '" stroke-width="1.6"/>';
        /* Garis rujukan mendatar */
        $s .= '<path d="M0 78h120" stroke="' . $ink . '" stroke-width=".8" stroke-dasharray="2 3"/>';

        return $s . '</svg>';
    };

    /* Lakaran bilik kecil untuk senarai hasil dalam mockup hero */
    $thumbRoom = function (int $seats) {
        $line = 'rgba(233,229,219,.42)';
        $s = '<svg class="mock__thumb" viewBox="0 0 52 40" aria-hidden="true">';
        $s .= '<rect width="52" height="40" fill="#132A44"/>';
        $s .= '<rect x="7.5" y="6.5" width="37" height="27" fill="none" stroke="' . $line . '" stroke-width="1.2"/>';
        $s .= '<rect x="19" y="15" width="14" height="10" rx="2" fill="rgba(192,138,46,.45)" stroke="rgba(192,138,46,.8)" stroke-width=".8"/>';
        for ($n = 0; $n < $seats; $n++) {
            $x = 20 + ($n % 3) * 5;
            $y = $n < 3 ? 11 : 27;
            $s .= '<rect x="' . $x . '" y="' . $y . '" width="3.4" height="2.4" rx=".8" fill="' . $line . '"/>';
        }
        $s .= '<path d="M21 6.5h10" stroke="rgba(192,138,46,.9)" stroke-width="1.6"/>';
        return $s . '</svg>';
    };

    $loginUrl    = Route::has('login')    ? route('login')    : '#mula';

    $facts = [
        ['16', 'Modul'],
        ['2',  'Domain'],
        ['8',  'Peranan'],
    ];

    $stats = [
        ['id' => 'OBJ-01', 'num' => '0',   'unit' => '',  'cap' => 'Pertindihan tempahan bilik selepas tiga bulan operasi'],
        ['id' => 'OBJ-02', 'num' => '50',  'unit' => '%', 'cap' => 'Penurunan tempahan tidak hadir dalam tempoh enam bulan'],
        ['id' => 'OBJ-03', 'num' => '100', 'unit' => '%', 'cap' => 'Aset ICT aktif berdaftar dan berlabel kod QR'],
        ['id' => 'OBJ-04', 'num' => '90',  'unit' => '%', 'cap' => 'Tiket keutamaan P2 ditutup dalam sasaran SLA'],
    ];

    $before = [
        'Tempahan melalui e-mel, WhatsApp dan buku log kaunter.',
        'Pertindihan bilik ditemui hanya pada saat mesyuarat bermula.',
        'Aduan kerosakan dibuat secara lisan, tanpa nombor rujukan.',
        'Rekod aset tersebar dalam beberapa fail Excel yang tidak selaras.',
        'Penyelenggaraan hanya dibuat selepas peralatan rosak.',
        'Laporan bulanan disusun secara manual setiap kali diminta.',
    ];

    $after = [
        'Satu kalendar berpusat dengan pengesanan konflik serta-merta.',
        'Slot bertindih ditolak sebelum borang dihantar, bukan selepas.',
        'Setiap aduan menerima nombor rujukan dan sasaran masa pemulihan.',
        'Satu daftar induk aset dengan sejarah pergerakan penuh.',
        'Perintah kerja pencegahan dijana automatik mengikut jadual.',
        'Laporan pengurusan dijana dalam bawah lima minit.',
    ];

    $domains = [
        [
            'stamp' => 'Domain A',
            'icon'  => 'calendar',
            'title' => 'Tempahan Bilik Mesyuarat',
            'illus' => $illusRoom,
            'lead'  => 'Daripada carian bilik sehingga pelepasan automatik. Kakitangan melihat hanya bilik yang benar-benar kosong, dan penanda "perlu kelulusan" dipaparkan sebelum borang diisi.',
            'feats' => [
                'Katalog bilik dengan kapasiti, susun atur dan kemudahan tetap',
                'Kalendar harian, mingguan dan bulanan serta tempahan berulang',
                'Aliran kelulusan boleh konfigurasi dengan pelulus ganti',
                'Daftar masuk kod QR di pintu bilik dan pelepasan automatik',
                'Permintaan sokongan mesyuarat: susun atur, minuman, peralatan',
            ],
            'mods'  => 'M04 · M05 · M06 · M07 · M08',
        ],
        [
            'stamp' => 'Domain B',
            'icon'  => 'wrench',
            'title' => 'Penyelenggaraan Aset ICT',
            'illus' => $illusAsset,
            'lead'  => 'Daftar induk bagi setiap komputer, pencetak dan penghala. Aduan kerosakan menjadi tiket bernombor rujukan dengan SLA yang boleh diukur, bukan panggilan telefon yang hilang.',
            'feats' => [
                'Inventari lengkap dengan nombor siri, waranti dan lokasi semasa',
                'Label kod QR bagi setiap aset dan sejarah pergerakan penuh',
                'Tiket kerosakan dengan agihan tugas dan pengiraan SLA',
                'Penyelenggaraan pencegahan berjadual mengikut kategori aset',
                'Vendor, kontrak sokongan dan stok alat ganti yang berkait tiket',
            ],
            'mods'  => 'M09 · M10 · M11 · M12 · M13',
        ],
    ];

    $layers = [
        [
            'name'    => 'Lapisan Asas',
            'note'    => 'Platform',
            'modules' => [
                ['M01', 'settings',  'Pentadbiran & Konfigurasi', 'Waktu operasi, ambang tidak hadir, sasaran SLA dan templat notifikasi tanpa menulis kod.'],
                ['M02', 'users',     'Pengguna, Peranan & Akses', 'Pengesahan melalui direktori organisasi dan kawalan akses berasaskan lapan peranan.'],
                ['M03', 'pin',       'Direktori Organisasi & Lokasi', 'Hierarki bahagian dan hierarki fizikal kampus, bangunan, tingkat serta ruang.'],
                ['M16', 'shield',    'Jejak Audit & Keselamatan', 'Merekod siapa, bila, nilai sebelum dan selepas. Log tidak boleh dipadam dari antara muka.'],
            ],
        ],
        [
            'name'    => 'Domain A — Tempahan Bilik',
            'note'    => 'Fasa 1',
            'modules' => [
                ['M04', 'monitor',   'Katalog Bilik & Sumber', 'Kapasiti, susun atur disokong, kemudahan tetap, foto dan peraturan khusus bilik.'],
                ['M05', 'calendar',  'Enjin Tempahan & Kalendar', 'Semakan ketersediaan, pengesanan konflik, tempahan berulang, pindaan dan pembatalan.'],
                ['M06', 'workflow',  'Kelulusan & Aliran Kerja', 'Menentukan siapa pelulus dan apa berlaku jika pelulus tidak bertindak dalam tempoh.'],
                ['M07', 'qr',        'Daftar Masuk & Pelepasan Auto', 'Pengesahan kehadiran melalui imbasan QR di pintu bilik atau butang dalam sistem.'],
                ['M08', 'clipboard', 'Perkhidmatan Sokongan Mesyuarat', 'Susun atur meja, minuman, peralatan tambahan dan sokongan teknikal semasa mesyuarat.'],
            ],
        ],
        [
            'name'    => 'Domain B — Aset ICT',
            'note'    => 'Fasa 2',
            'modules' => [
                ['M09', 'box',       'Inventari & Pendaftaran Aset', 'Nombor siri, tarikh perolehan, nilai, waranti, pemegang amanah dan lokasi semasa.'],
                ['M10', 'wrench',    'Tiket Aduan Kerosakan', 'Buka, agih, diagnosis, sahkan pemulihan dan tutup, dengan SLA mengikut keutamaan.'],
                ['M11', 'clock',     'Penyelenggaraan Berjadual', 'Perintah kerja pencegahan dijana automatik mengikut kategori dan kitaran aset.'],
                ['M12', 'truck',     'Vendor, Kontrak & Waranti', 'Amaran sebelum waranti tamat dan prestasi vendor berdasarkan masa tindak balas.'],
                ['M13', 'layers',    'Alat Ganti & Stok', 'Pengeluaran stok berkait tiket atau perintah kerja, dengan amaran paras stok rendah.'],
            ],
        ],
        [
            'name'    => 'Perkhidmatan Rentas',
            'note'    => 'Rentas domain',
            'modules' => [
                ['M14', 'bell',      'Notifikasi & Integrasi', 'E-mel, notifikasi dalam aplikasi dan jemputan kalendar format ICS daripada satu tempat.'],
                ['M15', 'chart',     'Laporan, Dashboard & Analitik', 'Papan pemuka mengikut peranan, eksport Excel dan PDF, serta penghantaran berjadual.'],
            ],
        ],
    ];

    $flows = [
        'tempahan' => [
            'label' => 'Tempah bilik mesyuarat',
            'steps' => [
                ['Cari slot', 'Pilih tarikh, masa mula, tempoh dan kapasiti. Sistem memaparkan hanya bilik yang benar-benar kosong.', 'Bawah 2 saat'],
                ['Pilih bilik', 'Setiap hasil menunjukkan lokasi, kemudahan dan penanda jika kelulusan diperlukan.', 'Sebelum borang'],
                ['Isi borang', 'Hanya dua medan wajib. Tarikh, masa dan bilik sudah ditetapkan pada langkah sebelumnya.', '2 medan wajib'],
                ['Kelulusan', 'Permohonan dihalakan kepada pelulus yang betul. Pelulus ganti mengambil alih jika bercuti.', 'Automatik'],
                ['Daftar masuk', 'Imbas QR di pintu bilik. Tiada pengesahan bermakna bilik dilepaskan semula.', 'Pelepasan auto'],
            ],
        ],
        'tiket' => [
            'label' => 'Lapor kerosakan ICT',
            'steps' => [
                ['Kenal pasti aset', 'Imbas kod QR pada peralatan, atau cari mengikut nombor siri dan lokasi.', 'Satu imbasan'],
                ['Hantar aduan', 'Sistem memberi nombor rujukan serta-merta dan anggaran masa penyelesaian.', 'TKT-YYYYMM-nnnnn'],
                ['Agihan tugas', 'Penyelia mengagihkan kepada juruteknik berdasarkan beban kerja dan lokasi.', 'Papan pemuka SLA'],
                ['Kerja pembaikan', 'Juruteknik merekod diagnosis, tindakan dan alat ganti yang dikeluarkan.', 'Mesra mudah alih'],
                ['Sahkan & tutup', 'Pengguna mengesahkan pemulihan. SLA dikira dari masa laporan hingga penutupan.', 'Jejak audit penuh'],
            ],
        ],
    ];

    $roles = [
        ['P1', 'Kakitangan Umum',    'Ahmad, Pegawai Tadbir',  'Tempah dalam bawah satu minit, dan tahu status aduan tanpa perlu bertanya sesiapa.'],
        ['P2', 'Setiausaha',         'Siti, Pembantu Tadbir',  'Tempah bagi pihak pegawai lain, uruskan mesyuarat berulang dan pinda tempahan dengan pantas.'],
        ['P3', 'Pelulus',            'Encik Rahman, Ketua Bahagian', 'Senarai tugas kelulusan yang jelas dan tiada tempahan tersekat ketika beliau bercuti.'],
        ['P4', 'Pentadbir Fasiliti', 'Puan Lina',              'Kawalan penuh ke atas katalog bilik dan peraturan, serta laporan penggunaan tanpa kerja manual.'],
        ['P5', 'Juruteknik ICT',     'Faiz, di lapangan',      'Senarai tugas hari ini, imbas QR untuk buka rekod aset, kemas kini status dalam beberapa ketikan.'],
        ['P6', 'Penyelia ICT',       'Encik Zul',              'Papan pemuka beban kerja dan SLA, pengagihan tugas pantas, laporan bulanan sedia guna.'],
        ['P7', 'Pegawai Aset',       'Puan Nor',               'Daftar aset lengkap, sejarah pergerakan, senarai pengauditan fizikal dan pelupusan berperingkat.'],
        ['P8', 'Pentadbir Sistem',   'Unit ICT',               'Konfigurasi tanpa menulis kod, jejak audit penuh, serta alat sandaran dan pemulihan.'],
    ];

    $phases = [
        ['Fasa 1', '10 minggu', 'Asas dan Tempahan', 'Tempahan bilik berfungsi penuh dengan kelulusan dan notifikasi e-mel. Memberi nilai kepada semua kakitangan sejak hari pertama.', ['M01','M02','M03','M04','M05','M06','M14','M16']],
        ['Fasa 2', '10 minggu', 'Aset dan Tiket',    'Inventari aset berlabel QR dan helpdesk kerosakan dengan pengiraan SLA. Memberi nilai kepada unit ICT.', ['M09','M10','M13','M15']],
        ['Fasa 3', '8 minggu',  'Pematangan',        'Pelepasan automatik, penyelenggaraan pencegahan, rekod vendor dan papan pemuka analitik penuh.', ['M07','M08','M11','M12','M14','M15']],
        ['Fasa 4', 'Belum ditetapkan', 'Pilihan',    'Aplikasi mudah alih natif, integrasi kewangan dan papan tanda digital bilik, tertakluk kajian faedah.', ['Kajian faedah']],
    ];

    $secure = [
        ['lock',     'Akses berasaskan peranan', 'Lapan peranan sistem dengan matriks kebenaran yang jelas bagi setiap modul, daripada cipta sehingga padam.'],
        ['shield',   'Jejak audit tidak boleh padam', 'Setiap perubahan data penting direkod: siapa, bila, nilai sebelum dan nilai selepas.'],
        ['database', 'Data kekal dalam negara', 'Hos dalam rangkaian dalaman atau awan tertutup organisasi, selaras kekangan kedaulatan data.'],
        ['clipboard','Pematuhan PDPA',          'Data peribadi kakitangan dikendalikan mengikut Akta Perlindungan Data Peribadi.'],
    ];

    $navLinks = [
        '#masalah'   => 'Masalah',
        '#domain'    => 'Domain',
        '#modul'     => 'Modul',
        '#aliran'    => 'Cara Kerja',
        '#peranan'   => 'Peranan',
        '#pelaksanaan' => 'Pelaksanaan',
    ];
@endphp

    {{-- ============================ NAVIGASI ============================ --}}
    <header class="nav" id="nav">
        <div class="wrap">
            <div class="nav__inner">
                <a href="#atas" class="brand" aria-label="SPFA — halaman utama">
                    <span class="brand__mark" aria-hidden="true">SP</span>
                    <span class="brand__text">
                        <span class="brand__name">SPFA</span>
                        <span class="brand__sub">Fasiliti &amp; Aset ICT</span>
                    </span>
                </a>

                <nav class="nav__links" aria-label="Navigasi utama">
                    @foreach ($navLinks as $href => $label)
                        <a href="{{ $href }}">{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="nav__cta">
                    <a href="{{ $loginUrl }}" class="btn btn--ghost-ink btn--sm">Log Masuk</a>
                    <a href="#mula" class="btn btn--primary btn--sm">
                        Mula Guna {!! $icon('arrow') !!}
                    </a>
                    <button class="nav__burger" id="burger" type="button"
                            aria-label="Buka menu" aria-expanded="false" aria-controls="navPanel">
                        {!! $icon('menu') !!}
                    </button>
                </div>
            </div>
        </div>

        <div class="nav__panel" id="navPanel">
            <div class="wrap">
                @foreach ($navLinks as $href => $label)
                    <a href="{{ $href }}">{{ $label }}</a>
                @endforeach
                <a href="{{ $loginUrl }}" class="btn btn--primary">Log Masuk {!! $icon('arrow') !!}</a>
            </div>
        </div>
    </header>

    <main id="atas">

        {{-- ============================== HERO ============================== --}}
        <section class="hero blueprint grain">
            <span class="hero__glow" aria-hidden="true"></span>
            <span class="hero__glow hero__glow--2" aria-hidden="true"></span>

            <div class="wrap">
                <div class="hero__grid">
                    <div>
                        <p class="hero__badge" data-rise style="--d:.05s">
                            <span class="pulse" aria-hidden="true"></span>
                            <span>Versi 1.0 · <b>Sistem Dalaman Organisasi</b></span>
                        </p>

                        <h1 class="display" data-rise style="--d:.14s">
                            Bilik mesyuarat dan aset ICT,<br>
                            <span class="accent">akhirnya dalam satu sistem.</span>
                        </h1>

                        <p class="lead" data-rise style="--d:.24s">
                            SPFA menggantikan buku log kaunter, e-mel tempahan dan aduan lisan dengan satu
                            platform berpusat. Tempah bilik tanpa pertindihan, lapor kerosakan dengan nombor
                            rujukan, dan jejak setiap aset ICT sehingga label QR yang terakhir.
                        </p>

                        <div class="hero__actions" data-rise style="--d:.34s">
                            <a href="{{ $loginUrl }}" class="btn btn--primary">
                                Log Masuk ke Sistem {!! $icon('arrow') !!}
                            </a>
                            <a href="#domain" class="btn btn--ghost-ink">Terokai dua domain</a>
                        </div>

                        <ul class="hero__facts" data-rise style="--d:.44s">
                            @foreach ($facts as [$num, $label])
                                <li>
                                    <b>{{ $num }}</b>
                                    <span>{{ $label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Mockup produk: skrin carian bilik --}}
                    <div class="mock" data-rise style="--d:.5s">
                        <div class="mock__app">
                            <div class="mock__bar">
                                <span class="mock__dot"></span>
                                <span class="mock__dot"></span>
                                <span class="mock__dot"></span>
                                <span class="mock__title">SPFA — CARI BILIK MESYUARAT</span>
                            </div>

                            <div class="mock__body">
                                <div class="mock__filters">
                                    <div class="mock__field"><i>Tarikh</i><b>10 Sep 2026</b></div>
                                    <div class="mock__field"><i>Masa</i><b>10:00 · 2 jam</b></div>
                                    <div class="mock__field"><i>Kapasiti</i><b>12 orang</b></div>
                                </div>

                                <div class="mock__strip">
                                    <div class="mock__strip-head">
                                        <span>08:00</span><span>KETERSEDIAAN</span><span>20:00</span>
                                    </div>
                                    <div class="mock__slots">
                                        @php
                                            $slots = ['','busy','busy','','pick','pick','','','busy','','',''];
                                        @endphp
                                        @foreach ($slots as $i => $s)
                                            <span class="mock__slot {{ $s ? 'mock__slot--' . $s : '' }}"
                                                  style="animation-delay: {{ 0.6 + $i * 0.045 }}s"></span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mock__rooms">
                                    @php
                                        $mockRooms = [
                                            ['BM-A-301', 'Bilik Mesyuarat Utama', 'Bangunan A, Tingkat 3 · Kapasiti 20', 6],
                                            ['BM-B-205', 'Bilik Perbincangan B',  'Bangunan B, Tingkat 2 · Kapasiti 14', 4],
                                        ];
                                    @endphp
                                    @foreach ($mockRooms as [$code, $name, $meta, $seats])
                                        <div class="mock__room">
                                            {!! $thumbRoom($seats) !!}
                                            <span class="mock__room-info">
                                                <span class="mock__code">{{ $code }}</span>
                                                <span class="mock__room-name">{{ $name }}</span>
                                                <span class="mock__room-meta">{{ $meta }}</span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Kad terapung: tiket kerosakan --}}
                        <div class="float float--ticket">
                            <p class="float__label">Tiket kerosakan</p>
                            <p class="float__ref">TKT-202609-00342</p>
                            <span class="chip chip--active">Dalam Tindakan</span>
                            <div class="float__sla">
                                <div class="float__sla-bar"><div class="float__sla-fill"></div></div>
                                <p class="float__sla-text">SLA P2 · baki 3j 12m</p>
                            </div>
                        </div>

                        {{-- Kad terapung: label QR aset --}}
                        <div class="float float--qr">
                            {!! $qr('qr') !!}
                            <div>
                                <p class="float__label">Label aset</p>
                                <p class="float__ref">AST-PC-004821</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================= JALUR OBJEKTIF ========================= --}}
        <section class="band" aria-label="Sasaran objektif projek">
            <div class="wrap">
                <div class="band__grid">
                    @foreach ($stats as $i => $s)
                        <div class="band__cell" data-id="{{ $s['id'] }}" data-reveal style="--d:{{ $i * .09 }}s">
                            <p class="band__num">
                                <span data-count="{{ $s['num'] }}">0</span><small>{{ $s['unit'] }}</small>
                            </p>
                            <p class="band__cap">{{ $s['cap'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ====================== MASALAH: SEBELUM / SELEPAS ====================== --}}
        <section class="section blueprint blueprint--paper" id="masalah">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">01 — Latar belakang masalah</p>
                    <h2 class="h2">Dua proses harian, <span class="accent">dikendalikan secara terpisah.</span></h2>
                    <p class="lead">
                        Tempahan bilik dan penyelenggaraan ICT sama-sama bergantung pada ingatan manusia dan
                        fail yang bertaburan. SPFA menyatukan kedua-duanya di atas struktur lokasi dan
                        pengguna yang sama.
                    </p>
                </div>

                <div class="compare">
                    <div class="compare__col compare__col--before" data-reveal style="--d:.06s">
                        <span class="compare__tag compare__tag--before">Keadaan semasa</span>
                        <ul class="compare__list">
                            @foreach ($before as $item)
                                <li>{!! $icon('cross') !!}<span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="compare__arrow" aria-hidden="true" data-reveal style="--d:.14s">
                        {!! $icon('arrow') !!}
                    </div>

                    <div class="compare__col compare__col--after" data-reveal style="--d:.2s">
                        <span class="compare__tag compare__tag--after">Dengan SPFA</span>
                        <ul class="compare__list">
                            @foreach ($after as $item)
                                <li>{!! $icon('check') !!}<span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================ DUA DOMAIN ============================ --}}
        <section class="section section--paper-2" id="domain">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">02 — Skop sistem</p>
                    <h2 class="h2">Dua domain, satu asas yang dikongsi.</h2>
                    <p class="lead">
                        Bilik mesyuarat dan komputer merujuk kepada hierarki lokasi yang sama, dan kepada
                        direktori pengguna yang sama. Satu perubahan lokasi berkuat kuasa di kedua-dua belah.
                    </p>
                </div>

                <div class="domains">
                    @foreach ($domains as $i => $d)
                        <article class="domain" data-reveal style="--d:{{ $i * .1 }}s">
                            <div class="domain__media">
                                {!! ($d['illus'])() !!}
                                <span class="domain__stamp">{!! $icon($d['icon']) !!}{{ $d['stamp'] }}</span>
                                <h3 class="domain__title">{{ $d['title'] }}</h3>
                            </div>

                            <div class="domain__body">
                                <p>{{ $d['lead'] }}</p>

                                <ul class="domain__feats">
                                    @foreach ($d['feats'] as $f)
                                        <li>{!! $icon('check') !!}<span>{{ $f }}</span></li>
                                    @endforeach
                                </ul>

                                <div class="domain__foot">
                                    <span class="domain__mods">{{ $d['mods'] }}</span>
                                    <a href="#modul" class="link-arrow">Lihat modul {!! $icon('arrow') !!}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================= MARQUEE ============================= --}}
        <div class="marquee" aria-hidden="true">
            <div class="marquee__track">
                @php
                    $ticker = [
                        'Katalog Bilik', 'Enjin Tempahan', 'Kelulusan Berperingkat', 'Daftar Masuk QR',
                        'Pelepasan Automatik', 'Inventari Aset', 'Tiket Kerosakan', 'Pengiraan SLA',
                        'Penyelenggaraan Pencegahan', 'Vendor & Waranti', 'Stok Alat Ganti',
                        'Notifikasi ICS', 'Papan Pemuka', 'Jejak Audit',
                    ];
                @endphp
                @for ($pass = 0; $pass < 2; $pass++)
                    @foreach ($ticker as $t)
                        <span class="marquee__item">{{ $t }}</span>
                    @endforeach
                @endfor
            </div>
        </div>

        {{-- ============================== MODUL ============================== --}}
        <section class="section section--ink blueprint grain" id="modul">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">03 — Seni bina modul</p>
                    <h2 class="h2">Enam belas modul, <span class="accent">satu tanggungjawab setiap satu.</span></h2>
                    <p class="lead">
                        Setiap modul mempunyai antara muka yang boleh diterangkan tanpa membaca dalamannya,
                        dan boleh diuji secara berasingan. Ini yang membolehkan pelaksanaan dipecah kepada fasa.
                    </p>
                </div>

                @foreach ($layers as $li => $layer)
                    <div class="layer" data-reveal style="--d:{{ $li * .06 }}s">
                        <div class="layer__head">
                            <h3 class="layer__name">{{ $layer['name'] }}</h3>
                            <span class="layer__count">{{ count($layer['modules']) }} modul · {{ $layer['note'] }}</span>
                        </div>

                        <div class="modules" style="--cols:{{ min(count($layer['modules']), 5) }}">
                            @foreach ($layer['modules'] as $mi => [$id, $ic, $name, $desc])
                                <article class="module" data-reveal style="--d:{{ $mi * .05 }}s">
                                    <p class="module__id">{{ $id }}</p>
                                    <span class="module__icon">{!! $icon($ic) !!}</span>
                                    <h4 class="module__name">{{ $name }}</h4>
                                    <p class="module__desc">{{ $desc }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================ ALIRAN KERJA ============================ --}}
        <section class="section blueprint blueprint--paper" id="aliran">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">04 — Cara ia berfungsi</p>
                    <h2 class="h2">Laluan biasa mesti pantas.</h2>
                    <p class="lead">
                        Menempah bilik dan melaporkan kerosakan adalah dua tindakan paling kerap dilakukan.
                        Kedua-duanya boleh dimulakan dari mana-mana skrin dalam satu ketikan.
                    </p>
                </div>

                <div class="tabs" role="tablist" aria-label="Pilih aliran kerja" data-reveal>
                    @foreach ($flows as $key => $flow)
                        <button class="tab" type="button" role="tab"
                                id="tab-{{ $key }}"
                                aria-controls="flow-{{ $key }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                tabindex="{{ $loop->first ? '0' : '-1' }}">
                            {{ $flow['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach ($flows as $key => $flow)
                    <div class="flow" id="flow-{{ $key }}" role="tabpanel"
                         aria-labelledby="tab-{{ $key }}" tabindex="0"
                         @if (! $loop->first) hidden @endif>
                        <div class="steps">
                            @foreach ($flow['steps'] as $si => [$title, $desc, $meta])
                                <div class="step" style="animation-delay:{{ $si * .09 }}s">
                                    <div class="step__rail">
                                        <span class="step__num">{{ str_pad($si + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="step__line" style="animation-delay:{{ $si * .09 }}s"></span>
                                    </div>
                                    <h3 class="step__title">{{ $title }}</h3>
                                    <p class="step__desc">{{ $desc }}</p>
                                    <p class="step__meta">{{ $meta }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================= CIRI UTAMA ============================= --}}
        <section class="section section--paper-2" id="ciri">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">05 — Ciri yang membezakan</p>
                    <h2 class="h2">Bahagian yang biasanya <span class="accent">terlepas pandang.</span></h2>
                    <p class="lead">
                        Sistem tempahan mudah dibina. Yang sukar ialah menangani bilik yang ditempah tetapi
                        tidak digunakan, dan mengukur pemulihan kerosakan dengan jujur.
                    </p>
                </div>

                <div class="bento">
                    {{-- Daftar masuk QR + pelepasan automatik --}}
                    <article class="tile tile--wide tile--dark" data-reveal>
                        <span class="tile__icon">{!! $icon('qr') !!}</span>
                        <h3 class="tile__title">Daftar masuk QR dan pelepasan automatik</h3>
                        <p class="tile__desc">
                            Penempah mengesahkan kehadiran dengan mengimbas kod QR di pintu bilik. Tiada
                            pengesahan dalam tempoh anjal bermakna tempahan dilepaskan dan bilik terbuka semula
                            kepada pengguna lain.
                        </p>
                        <div class="tile__demo">
                            <div class="tag">
                                {!! $qr('tag__qr') !!}
                                <span class="tag__meta">
                                    <span class="tag__code">BM-A-301</span>
                                    <span class="tag__desc">Imbas untuk daftar masuk · tetingkap 10 minit</span>
                                </span>
                            </div>
                        </div>
                    </article>

                    {{-- SLA --}}
                    <article class="tile tile--wide" data-reveal style="--d:.08s">
                        <span class="tile__icon">{!! $icon('clock') !!}</span>
                        <h3 class="tile__title">SLA yang benar-benar diukur</h3>
                        <p class="tile__desc">
                            Sasaran pemulihan ditetapkan mengikut keutamaan tiket dan dikira daripada masa
                            laporan hingga penutupan. Penyelia melihat tiket yang hampir melanggar sebelum ia berlaku.
                        </p>
                        <div class="tile__demo">
                            <div class="sla">
                                @php
                                    $slaRows = [
                                        ['P1', '4 jam',  '96%', 'var(--st-done)',   .96],
                                        ['P2', '8 jam',  '91%', 'var(--st-done)',   .91],
                                        ['P3', '3 hari', '78%', 'var(--st-warn)',   .78],
                                    ];
                                @endphp
                                @foreach ($slaRows as $ri => [$pri, $target, $val, $col, $w])
                                    <div class="sla__row">
                                        <span class="sla__pri">{{ $pri }} · {{ $target }}</span>
                                        <span class="sla__track">
                                            <span class="sla__fill"
                                                  style="width:{{ $w * 100 }}%;background:{{ $col }};transition-delay:{{ $ri * .12 }}s"></span>
                                        </span>
                                        <span class="sla__val">{{ $val }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    {{-- Notifikasi --}}
                    <article class="tile tile--third" data-reveal style="--d:.04s">
                        <span class="tile__icon">{!! $icon('bell') !!}</span>
                        <h3 class="tile__title">Notifikasi dari satu tempat</h3>
                        <p class="tile__desc">
                            Satu-satunya modul yang menghantar mesej keluar, jadi templat kekal konsisten.
                        </p>
                        <div class="tile__demo">
                            <ul class="notif">
                                <li>{!! $icon('mail') !!}<span>Pengesahan tempahan <code>TMP-202609-00187</code></span></li>
                                <li>{!! $icon('calendar') !!}<span>Jemputan kalendar format ICS</span></li>
                                <li>{!! $icon('bell') !!}<span>Amaran waranti tamat dalam 30 hari</span></li>
                            </ul>
                        </div>
                    </article>

                    {{-- Penyelenggaraan pencegahan --}}
                    <article class="tile tile--third" data-reveal style="--d:.12s">
                        <span class="tile__icon">{!! $icon('clipboard') !!}</span>
                        <h3 class="tile__title">Penyelenggaraan sebelum rosak</h3>
                        <p class="tile__desc">
                            Pelan berkala mengikut kategori aset, contohnya pembersihan pencetak setiap suku
                            tahun. Perintah kerja dijana automatik dan pematuhan dijejak.
                        </p>
                    </article>

                    {{-- Laporan --}}
                    <article class="tile tile--third" data-reveal style="--d:.2s">
                        <span class="tile__icon">{!! $icon('chart') !!}</span>
                        <h3 class="tile__title">Laporan dalam bawah lima minit</h3>
                        <p class="tile__desc">
                            Papan pemuka mengikut peranan, eksport Excel dan PDF, serta penghantaran berjadual
                            melalui e-mel tanpa penyusunan manual.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ============================== PERANAN ============================== --}}
        <section class="section blueprint blueprint--paper" id="peranan">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">06 — Untuk siapa</p>
                    <h2 class="h2">Lapan peranan, lapan keperluan berbeza.</h2>
                    <p class="lead">
                        Juruteknik bekerja di lapangan dengan telefon di satu tangan. Pelulus tidak mahu log
                        masuk semata-mata untuk meluluskan sesuatu. Setiap skrin direka untuk persona yang
                        akan menggunakannya.
                    </p>
                </div>

                <div class="roles">
                    @foreach ($roles as $ri => [$id, $name, $who, $need])
                        <article class="role" data-reveal style="--d:{{ ($ri % 4) * .07 }}s">
                            <div class="role__media">
                                {!! $illusPerson($id, $ri) !!}
                                <span class="role__id">{{ $id }}</span>
                            </div>
                            <div class="role__body">
                                <h3 class="role__name">{{ $name }}</h3>
                                <p class="role__who">{{ $who }}</p>
                                <p class="role__need">{{ $need }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================ PELAKSANAAN ============================ --}}
        <section class="section section--ink blueprint grain" id="pelaksanaan">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">07 — Pelan pelaksanaan</p>
                    <h2 class="h2">Setiap fasa menghasilkan sesuatu <span class="accent">yang benar-benar boleh diguna.</span></h2>
                    <p class="lead">
                        Bukan separuh sistem yang menunggu fasa berikutnya. Fasa 1 memberi nilai kepada semua
                        kakitangan. Fasa 2 memberi nilai kepada unit ICT.
                    </p>
                </div>

                <div class="phases">
                    @foreach ($phases as $pi => [$id, $dur, $title, $desc, $tags])
                        <article class="phase" data-reveal style="--d:{{ $pi * .07 }}s">
                            <p class="phase__id">
                                {{ $id }}
                                <span class="phase__dur">{{ $dur }}</span>
                            </p>
                            <span class="phase__mark" aria-hidden="true"><span class="phase__bullet"></span></span>
                            <div>
                                <h3 class="phase__title">{{ $title }}</h3>
                                <p class="phase__desc">{{ $desc }}</p>
                            </div>
                            <div class="phase__tags">
                                @foreach ($tags as $tag)
                                    <span>{{ $tag }}</span>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- =========================== KESELAMATAN =========================== --}}
        <section class="section section--paper-2" id="keselamatan">
            <div class="wrap">
                <div class="section-head" data-reveal>
                    <p class="eyebrow">08 — Keselamatan &amp; pematuhan</p>
                    <h2 class="h2">Data kakitangan, dikendalikan sewajarnya.</h2>
                </div>

                <div class="secure">
                    @foreach ($secure as $si => [$ic, $title, $desc])
                        <article class="secure__item" data-reveal style="--d:{{ $si * .07 }}s">
                            {!! $icon($ic) !!}
                            <h3>{{ $title }}</h3>
                            <p>{{ $desc }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- =============================== CTA =============================== --}}
        <section class="section cta grain" id="mula">
            <span class="cta__ring" aria-hidden="true"></span>
            <span class="cta__ring cta__ring--2" aria-hidden="true"></span>
            <span class="cta__ring cta__ring--3" aria-hidden="true"></span>

            <div class="wrap">
                <div class="cta__inner" data-reveal>
                    <p class="eyebrow" style="justify-content:center">Sedia untuk bermula</p>
                    <h2 class="h2">Log masuk dengan akaun organisasi anda.</h2>
                    <p class="lead">
                        SPFA menggunakan direktori pengguna sedia ada. Tiada kata laluan baharu untuk diingat,
                        dan peranan anda ditetapkan secara automatik mengikut unit.
                    </p>
                    <div class="cta__actions">
                        <a href="{{ $loginUrl }}" class="btn btn--primary">Log Masuk {!! $icon('arrow') !!}</a>
                        <a href="#modul" class="btn btn--ghost-ink">Semak senarai modul</a>
                    </div>
                    <p class="cta__note">Sokongan · Unit ICT, samb. 1200 · helpdesk dalaman</p>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================== FOOTER ============================== --}}
    <footer class="foot">
        <div class="wrap">
            <div class="foot__grid">
                <div>
                    <a href="#atas" class="brand">
                        <span class="brand__mark" aria-hidden="true">SP</span>
                        <span class="brand__text">
                            <span class="brand__name">SPFA</span>
                            <span class="brand__sub">Fasiliti &amp; Aset ICT</span>
                        </span>
                    </a>
                    <p class="foot__about">
                        Sistem Pengurusan Fasiliti &amp; Aset ICT. Aplikasi web dalaman untuk tempahan bilik
                        mesyuarat dan penyelenggaraan peralatan ICT organisasi.
                    </p>
                </div>

                <div>
                    <h4>Domain A</h4>
                    <div class="foot__links">
                        <a href="#domain">Katalog bilik</a>
                        <a href="#aliran">Enjin tempahan</a>
                        <a href="#modul">Kelulusan</a>
                        <a href="#ciri">Daftar masuk QR</a>
                    </div>
                </div>

                <div>
                    <h4>Domain B</h4>
                    <div class="foot__links">
                        <a href="#domain">Inventari aset</a>
                        <a href="#aliran">Tiket kerosakan</a>
                        <a href="#ciri">Penyelenggaraan</a>
                        <a href="#modul">Vendor &amp; stok</a>
                    </div>
                </div>

                <div>
                    <h4>Sistem</h4>
                    <div class="foot__links">
                        <a href="#peranan">Peranan pengguna</a>
                        <a href="#pelaksanaan">Pelan fasa</a>
                        <a href="#keselamatan">Keselamatan</a>
                        <a href="{{ $loginUrl }}">Log masuk</a>
                    </div>
                </div>
            </div>

            <div class="foot__bottom">
                <span>&copy; {{ date('Y') }} SPFA · Unit ICT &amp; Unit Pentadbiran</span>
                <span>Versi 1.0 (Draf) · Dibina dengan Laravel {{ Illuminate\Foundation\Application::VERSION }}</span>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            'use strict';

            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var nav = document.getElementById('nav');

            /* --- Menu mudah alih --- */
            var burger = document.getElementById('burger');
            var panel = document.getElementById('navPanel');

            burger.addEventListener('click', function () {
                var open = panel.classList.toggle('is-open');
                burger.setAttribute('aria-expanded', String(open));
                burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
            });

            panel.addEventListener('click', function (e) {
                if (e.target.tagName === 'A') {
                    panel.classList.remove('is-open');
                    burger.setAttribute('aria-expanded', 'false');
                }
            });

            /* --- Kiraan angka objektif --- */
            var runCount = function (el) {
                var target = parseInt(el.getAttribute('data-count'), 10);

                if (reduced || target === 0) {
                    el.textContent = String(target);
                    return;
                }

                var duration = 1400;
                var start = null;

                var tick = function (now) {
                    if (start === null) start = now;
                    var p = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = String(Math.round(target * eased));
                    if (p < 1) requestAnimationFrame(tick);
                };

                requestAnimationFrame(tick);
            };

            /* --- Pendedahan semasa skrol dan kiraan angka ---
               Dua senarai tertunggak yang mengecil sehingga kosong. Pemerhati
               menangani skrol biasa; sapuan pada peristiwa skrol menangkap
               lompatan besar (pautan sauh, kekunci End, pemulihan kedudukan
               skrol) yang boleh melangkau pemerhati sepenuhnya. */
            var pendingReveal = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
            var pendingCount  = Array.prototype.slice.call(document.querySelectorAll('[data-count]'));

            var take = function (list, el) {
                var i = list.indexOf(el);
                if (i === -1) return false;
                list.splice(i, 1);
                return true;
            };

            var showReveal = function (el) {
                if (take(pendingReveal, el)) el.classList.add('is-in');
            };

            var showCount = function (el) {
                if (take(pendingCount, el)) runCount(el);
            };

            if (reduced || !('IntersectionObserver' in window)) {
                pendingReveal.slice().forEach(showReveal);
                pendingCount.slice().forEach(showCount);
            } else {
                var observe = function (list, handler, options) {
                    var obs = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                handler(entry.target);
                                obs.unobserve(entry.target);
                            }
                        });
                    }, options);

                    list.forEach(function (el) { obs.observe(el); });
                };

                observe(pendingReveal, showReveal, { rootMargin: '0px 0px -12% 0px', threshold: 0.12 });
                observe(pendingCount, showCount, { threshold: 0.6 });
            }

            var sweep = function () {
                if (!pendingReveal.length && !pendingCount.length) return;
                var limit = window.innerHeight * 0.88;

                pendingReveal.slice().forEach(function (el) {
                    if (el.getBoundingClientRect().top < limit) showReveal(el);
                });
                pendingCount.slice().forEach(function (el) {
                    if (el.getBoundingClientRect().top < limit) showCount(el);
                });
            };

            /* --- Peristiwa skrol: keadaan navigasi + sapuan, dicantum satu bingkai --- */
            var ticking = false;

            var onScroll = function () {
                nav.classList.toggle('is-stuck', window.scrollY > 24);

                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function () {
                    ticking = false;
                    sweep();
                });
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll, { passive: true });

            /* --- Tab aliran kerja --- */
            var tabs = Array.prototype.slice.call(document.querySelectorAll('[role="tab"]'));

            var selectTab = function (tab, focus) {
                tabs.forEach(function (t) {
                    var active = t === tab;
                    t.setAttribute('aria-selected', String(active));
                    t.setAttribute('tabindex', active ? '0' : '-1');
                    document.getElementById(t.getAttribute('aria-controls')).hidden = !active;
                });

                if (focus) tab.focus();

                /* Mainkan semula animasi langkah bagi panel yang dipilih */
                var panelEl = document.getElementById(tab.getAttribute('aria-controls'));
                panelEl.querySelectorAll('.step').forEach(function (step) {
                    step.style.animation = 'none';
                    void step.offsetWidth;
                    step.style.animation = '';
                });
            };

            tabs.forEach(function (tab, i) {
                tab.addEventListener('click', function () { selectTab(tab, false); });

                tab.addEventListener('keydown', function (e) {
                    var next = null;
                    if (e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
                    if (e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
                    if (next) {
                        e.preventDefault();
                        selectTab(next, true);
                    }
                });
            });

        })();
    </script>
</body>
</html>
