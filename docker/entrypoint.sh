#!/bin/sh
set -eu

php scripts/bootstrap_administrador_inicial.php

exec apache2-foreground
