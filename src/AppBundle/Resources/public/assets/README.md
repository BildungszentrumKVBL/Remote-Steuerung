# Assets

Here are all assets .png, .xml, .json and .svg that are publicly available.


## Icons

Favicon, Android, Apple and Mstile images are located here.

\latexonly
    \includegraphics[scale=4]{./../../src/AppBundle/Resources/public/assets/android-chrome-192x192.png}
\endlatexonly


## Browserconfig for Metro-UI

\latexonly
\inputminted{xml}{./../../src/AppBundle/Resources/public/assets/browserconfig.xml}
\pagebreak
\endlatexonly


## Offline website

This file is being serve by `AppController::jsonManifestAction()`

\latexonly
\inputminted{JavaScript}{./../../src/AppBundle/Resources/public/assets/manifest.json}
\pagebreak
\endlatexonly


## Safari pinned tab

This file is a simple SVG file that is being displayed in the Safari browser when the tab is pinned.
The svg can only contain 1 color.

\latexonly
{\footnotesize{\inputminted{xml}{./../../src/AppBundle/Resources/public/assets/safari-pinned-tab.svg}}}
\pagebreak
\endlatexonly
