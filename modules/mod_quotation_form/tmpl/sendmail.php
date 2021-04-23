<?php
define('_JEXEC', 1);

// defining the base path.
if (stristr($_SERVER['SERVER_SOFTWARE'], 'win32')) {
    define('JPATH_BASE', realpath(dirname(__FILE__) . '\..\..\..'));
} else define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../..'));
define('DS', DIRECTORY_SEPARATOR);

// including the main joomla files
require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

// Creating an app instance
$app = JFactory::getApplication('site');
$app->initialise();
jimport('joomla.user.user');
jimport('joomla.user.helper');

$de = $_POST['de'];
$origem = $_POST['origem'];
$destino = $_POST['destino'];
$cuidado = $_POST['cuidado'];
$peso_bruto = $_POST['peso_bruto'];
$quantidade = $_POST['quantidade'];
$comprimento = $_POST['comprimento'];
$largura = $_POST['largura'];
$altura = $_POST['altura'];
$descricao = $_POST['descricao'];
$embalagem_observacoes = $_POST['embalagem_observacoes'];
$anexo = $_FILES['anexo'];
$empresa = $_POST['empresa'];
$reponsavel = $_POST['reponsavel'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$email_admin = $_POST['email_admin'];
$subject = $_POST['subject'];

try {

    $uploadDir = JPATH_BASE . DS . 'images' . DS . 'anexos/';
    $file = $uploadDir . basename($anexo["name"]);
    $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!move_uploaded_file($anexo["tmp_name"], $file)) {
        throw new Exception('Erro no upload da imagem!', 1);
    }

    $app = JFactory::getApplication();

    if (empty($email_admin)) {
        $mailfrom = $app->get('mailfrom');
    } else {
        $mailfrom = $email_admin;
    }

    $fromname = $app->get('fromname');
    $sitename = $app->get('sitename');

    if (!empty($subject)) {
        $subject = $subject;
    } else {
        $subject = 'Cotação realizado através do site ' . $app->get('sitename');
    }

    /* CORPO DO E-MAIL */
    $body = '<h3>Dados do Solicitante</h3>';
    $body .= '<p><b>Empresa:</b>' . $empresa . '<br>';
    $body .= '<b>Responsável:</b>' . $reponsavel . '<br>';
    $body .= '<b>Telefone:</b>' . $telefone . '<br>';
    $body .= '<b>E-mail:</b>' . $email . '</p>';

    $body .= '<h3>Previsão de Embarque</h3>';
    $body .= '<p><b>De:</b>' . $de . '<br>';
    $body .= '<b>Origem:</b>' . $origem . '<br>';
    $body .= '<b>Destino:</b>' . $destino . '<br>';
    $body .= '<b>Cuidado:</b>' . $cuidado . '<br>';
    $body .= '<b>Peso Bruto(kg):</b>' . $peso_bruto . '</p>';

    $body .= '<h3>Volumes</h3>';
    $body .= '<p><b>Quantidade:</b>' . $quantidade . '<br>';
    $body .= '<b>Comprimento(cm):</b>' . $comprimento . '<br>';
    $body .= '<b>Largura(cm):</b>' . $largura . '<br>';
    $body .= '<b>Altura(cm):</b>' . $altura . '</p>';

    $body .= '<h3>Detalhes</h3>';
    $body .= '<p><b>Descrição:</b>' . $descricao . '<br>';
    $body .= '<b>Embalagem/Observações:</b>' . $embalagem_observacoes . '<br>';
    if (!empty($anexo['name'])) {
        $body .= '<p>Anexo:</p>';
        $body .= '<img src="' . JPATH_BASE . DS . 'images' . DS . $anexo['name'] . '" />';
    }

    $mail = JFactory::getMailer();
    $mail->addRecipient($mailfrom);
    $mail->addReplyTo(array($email, $reponsavel));
    $mail->setSender(array($mailfrom, $fromname));
    $mail->isHtml();
    $mail->setSubject($subject);
    $mail->setBody($body);
    $sent = $mail->Send();

    $response['msg'] = 'Cotação enviada com sucesso!<br>Em breve responderemos sua solitação';
    $response['class'] = 'success';
} catch (phpmailerException $e) {
    $response['class'] = 'danger';
    $response['msg'] = $e->message;
}

echo json_encode($response);
