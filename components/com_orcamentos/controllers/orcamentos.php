<?php

/**

 * @version		$Id: contact.php 21991 2011-08-18 15:43:40Z infograf768 $

 * @package		Joomla.Site

 * @subpackage	Contact

 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.

 * @license		GNU General Public License version 2 or later; see LICENSE.txt

 */



defined('_JEXEC') or die;



jimport('joomla.application.component.controllerform');

class OrcamentosControllerOrcamentos extends JControllerForm

{

	public function enviarorcamento()

	{

		// Check for request forgeries.



		// Initialise variables.

		$model           = $this->getModel('orcamentos');

		$params          = JComponentHelper::getParams('com_orcamentos');


		$de              = JRequest::getVar('de');

        $ate             = JRequest::getVar('ate');

        $origem          = JRequest::getVar('origem');

		$destino         = JRequest::getVar('destino');

		$cuidado         = JRequest::getVar('cuidado');

		$peso_bruto      = JRequest::getVar('peso_bruto');

		$quantidade      = JRequest::getVar('quantidade');

		$comprimento     = JRequest::getVar('comprimento');

        $largura         = JRequest::getVar('largura');

        $altura          = JRequest::getVar('altura');

        $descricao_carga = JRequest::getVar('nome');

        $embalagem       = JRequest::getVar('tel');

        $empresa         = JRequest::getVar('empresa');

        $responsavel     = JRequest::getVar('responsavel');

        $telefone        = JRequest::getVar('tel');

        $email           = JRequest::getVar('email');


             PHP_email::email_orcamento($de, $ate, $origem, $destino, $cuidado, $peso_bruto, $quantidade, $comprimento, $largura, $altura, $descricao_carga, $embalagem, 
					                    $empresa, $responsavel, $telefone, $email);

	return true;

	}

}

$doc = JFactory::getDocument();

$doc->addStyleSheet('components/com_orcamentos/css/styleorcamentos.css');



class PHP_email extends PHPMailer{



        function email_orcamento($de, $ate, $origem, $destino, $cuidado, $peso_bruto, $quantidade, $comprimento, $largura, $altura, $descricao_carga, $embalagem, 
                                 $empresa, $responsavel, $telefone, $email){



                     $app		= JFactory::getApplication();

			$mailfrom	= 'contato@horizontecargo.com.br';

			$fromname	= 'Cotação Horizonte Cargo - Site';

			$sitename	= $app->getCfg('sitename');



                        $mail = JFactory::getMailer();

			$mail->addRecipient($mailfrom);

		//	$mail->addReplyTo(array($email, $nomeconvite));

			$mail->setSender(array($mailfrom, $fromname));

                        $mail->IsHTML();

			$mail->setSubject("Cotação Horizonte Cargo");

			$mail->setBody('<html>

                                            <body>

                                                <table width="55%" align="center">

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>De</b>' .  $de .' <b>até</b>' .  $ate .' 

                                                        </font>

                                                        </td>

                                                    </tr>
                                                    

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Telefone: </b>' . $telefone . '

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>E-mail:</b> ' . $email . '

                                                        </font>

                                                        </td>

                                                    </tr>


                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Edificio: </b> ' . $edificio . '

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Endereco: </b> ' . $endereco .'-'. $bairro .'/'. $cidade .'

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>CEP: </b> ' . $cep . '

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Apartamentos: </b> ' . $aptos . '

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Salas: </b> ' . $salas . '

                                                        </font>

                                                        </td>

                                                    </tr>

							   <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Garagens: </b> ' . $garagens . '

                                                        </font>

                                                        </td>

                                                    </tr>

							   <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Zeladores: </b> ' . $qtdzeladores . '<b>Jornada: </b> ' . $jornadazelador . '

                                                        </font>

                                                        </td>

                                                    </tr>
							   
                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Porteiros: </b> ' . $qtdporteiros . '<b>Jornada: </b> ' . $jornadaporteiros . '

                                                        </font>

                                                        </td>

                                                    </tr>

							   <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Faxineiras: </b> ' . $qtdfaxineiras . '<b>Jornada: </b> ' . $jornadafaxineiras . '

                                                        </font>

                                                        </td>

                                                    </tr>

 							   <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Qtd. Outros: </b> ' . $qtdoutros . '<b>Jornada: </b> ' . $jornadautros . '

                                                        </font>

                                                        </td>

                                                    </tr>

							   <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Contratados por Conservadora: </b>' . $qtdfuncionarios . '

                                                        </font>

                                                        </td>

                                                    </tr>


                                                    <tr>

                                                        <td align=left colspan=2>

                                                        <font size="4" face="Verdana" color="#0F0F73">

                                                            <b>Data do Envio: </b>' . date ("d/m/Y H:i:s ") . '

                                                        </font>

                                                        </td>

                                                    </tr>

                                                    <tr>

                                                    </tr>

                                                </table>

                                            </body>

                                        </html>');

                   //    $sent = $mail->Send();

                       echo '<div class="confirm"><h1>Confirma&ccedil;&atilde;o Enviada com Sucesso.</h1><h1>Obrigado!</h1><br /><a href="index.php" rel="pagina inicial Lumiar Cerimonial">Voltar</a></div>';



	}

                

}