<?php

require __DIR__ . "/../config/erros.php";
require __DIR__ . "/../controller/router.php";

configurar_tratamento_de_erros();
rotear_requisicao($_SERVER["REQUEST_URI"]);
