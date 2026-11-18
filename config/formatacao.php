<?php

function formatar_valor_pedido($valor)
{
    return number_format((float) $valor, 2, ",", ".");
}
