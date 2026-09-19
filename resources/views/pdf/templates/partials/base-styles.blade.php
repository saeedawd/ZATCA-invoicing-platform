{{-- Shared font + base helpers for DomPDF invoice templates --}}
@font-face {
    font-family: 'tahoma';
    font-style: normal;
    font-weight: normal;
    src: url('file://{{ $fontRegular }}') format('truetype');
}
@font-face {
    font-family: 'tahoma';
    font-style: normal;
    font-weight: bold;
    src: url('file://{{ $fontBold }}') format('truetype');
}

body {
    font-family: 'tahoma', DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1c2621;
    line-height: 1.5;
    direction: ltr;
    margin: 0;
}

table { width: 100%; border-collapse: collapse; }
td, th { vertical-align: top; }

.ar { direction: ltr; text-align: right; }
.center { text-align: center; }
.middle { vertical-align: middle !important; }
.bold { font-weight: bold; }
.muted { color: #6d7a73; }
.tiny { font-size: 8px; }
.small { font-size: 10px; }
.h1 { font-size: 18px; font-weight: bold; }
.h2 { font-size: 13px; font-weight: bold; }
.gap { height: 10px; }
.gap-sm { height: 6px; }

.logo-img {
    max-height: 52px;
    max-width: 140px;
}
