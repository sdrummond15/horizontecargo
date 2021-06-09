<?php
defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">
    function checarEmail() {
        if (document.forms[0].email.value == "" ||
            document.forms[0].email.value.indexOf('@') == -1 ||
            document.forms[0].email.value.indexOf('.') == -1) {
            alert("Por favor, informe um E-MAIL válido!");
            return false;
        }
    }
</script>

<script type="text/javascript">
    /* Máscaras TELEFONE */
    function mascara(o, f) {
        v_obj = o
        v_fun = f
        setTimeout("execmascara()", 1)
    }

    function execmascara() {
        v_obj.value = v_fun(v_obj.value)
    }

    function mtel(v) {
        v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
        v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
        v = v.replace(/(\d)(\d{4})$/, "$1-$2"); //Coloca hífen entre o quarto e o quinto dígitos
        return v;
    }

    function id(el) {
        return document.getElementById(el);
    }
    /*TELEFONE 1*/
    window.onload = function() {
        id('telefone').onkeyup = function() {
            mascara(this, mtel);
        }
        id('celular').onkeyup = function() {
            mascara(this, mtel);
        }
    }
</script>

<script type="text/javascript">
    document.getElementById("uploadBtn").onchange = function() {
        document.getElementById("uploadFile").value = this.value;
    };
</script>

<div class="orcamentos-form">
    <h1>Fa&ccedil;a uma Cota&ccedil;&atilde;o Online</h1>
    <form id="orcamento-form" action="<?php echo JRoute::_('index.php?option=com_orcamentos&view=orcamentos&layout=default'); ?>" method="post" class="form-vainsereformlidate form-horizontal" enctype="multipart/form-data">

        <div class="dados">
            <input type="date" name="de" id="de" required="true" placeholder="De" />
            <input type="date" name="ate" id="ate" required="true" placeholder="Até" />
            <input type="text" name="origem" id="origem" placeholder="Origem" />
            <input type="text" name="destino" id="destino" placeholder="Destino" />
            <select name="cuidado" id="cuidado" form="cuidado">
                <option value="Basic">Basic</option>
                <option value="Pharma Passive">Pharma Passive</option>
                <option value="Perishable">Perishable</option>
                <option value="Hazmat(DGR)">Hazmat(DGR)</option>
                <option value="Secure">Secure</option>
                <option value="Alive">Alive</option>
                <option value="Oversize">Oversize</option>
                <option value="Human Remains">Human Remains</option>
                <option value="Couriers">Couriers</option>
            </select>
        </div>

        <div class="unidades">
            <div>
                <label>Peso Bruto (kg)</label>
                <input type="number" name="peso_bruto" id="peso_bruto" />
            </div>
        </div>

        <div class="unidades">
        <h4>Volume</h4>
            <div>
                <label>Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" />
            </div>
            <div>
                <label>Comprimento(cm)</label>
                <input type="number" name="comprimento" id="comprimento" />
            </div>
            <div>
                <label>Largura(cm)</label>
                <input type="number" name="largura" id="largura" />
            </div>
            <input type="text" name="descricao_carga" id="descricao_carga" placeholder="Descri&ccedil;&atilde;o da carga" />
            <input type="text" name="embalagem" id="embalagem" placeholder="Embalagem Observa&ccedil;&otilde;es" />
        </div>

        <div class="funcionarios">
            <h4>Zeladores</h4>
            <input type="text" name="empresa" id="empresa" placeholder="<?php echo utf8_encode('Empresa'); ?>" />
            <input type="text" name="responsavel" id="responsavel" placeholder="Respons&aacute;vel" />
            <input type="text" name="tel" id="telefone" maxlength="15" placeholder="Telefone" />
            <input type="email" name="email" class="input" id="email" required="true" onblur="checarEmail();" placeholder="E-mail" />
            
        </div>

        <input type="hidden" name="option" value="com_orcamentos" />
        <input type="hidden" name="task" value="orcamentos.enviarorcamento" />
        <input type="submit" value="Enviar Cota&ccedil;&atilde;o" class="submitform" />

    </form>
</div>