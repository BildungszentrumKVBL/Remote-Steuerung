#!/usr/bin/env bash

rm -rf doc/latex
doxygen
cd doc/latex/
pdflatex -shell-escape refman.tex
pdflatex -shell-escape refman.tex
open refman.pdf
cd ../..
