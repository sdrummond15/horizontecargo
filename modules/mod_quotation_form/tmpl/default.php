<section id="quotation">
    <div class="quotation">
        <div id="retornoHTML">
            <div id="msg-box" class="alert alert-danger" role="alert">
                <span class="fechar"><i class="fa fa-times" aria-hidden="true"></i></span>
                <div id="msg"></div>
            </div>
            <form id="form-quotation" method="post" enctype="multipart/form-data">
                <fieldset>
                    <h3>Previsão de Embarque</h3>
                    <div class="control-group">
                        <div class="controls">
                            <label for="de">De:</label>
                            <input id="de" name="de" type="date" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label for="ate">Até:</label>
                            <input id="ate" name="ate" type="date">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="origem" name="origem" type="text" placeholder="Origem" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="destino" name="destino" type="text" placeholder="Destino" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label for="cuidado">Cuidado:</label>
                            <select id="cuidado" name="cuidado" required>
                                <option value="Basic">Basic</option>
                                <option value="Pharma Passive">Pharma Passive</option>
                                <option value="Perishable">Perishable</option>
                                <option value="Hazmat (DGR)">Hazmat (DGR)</option>
                                <option value="Secure">Secure</option>
                                <option value="Alive">Alive</option>
                                <option value="Oversize">Oversize</option>
                                <option value="Human Remains">Human Remains</option>
                                <option value="Courier">Courier</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="peso_bruto" name="peso_bruto" type="number" placeholder="Peso Bruto (kg)" required>
                        </div>
                    </div>

                    <h3>Volumes</h3>
                    <div class="control-group">
                        <div class="controls">
                            <input id="quantidade" name="quantidade" type="number" placeholder="Quantidade" min="0" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="comprimento" name="comprimento" type="number" placeholder="Comprimento (cm)" min="0" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="largura" name="largura" type="number" placeholder="Largura (cm)" min="0" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="altura" name="altura" type="number" placeholder="Altura (cm)" min="0" required>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <h3>Detalhes</h3>
                    <div class="control-group">
                        <div class="controls">
                            <input id="descricao" name="descricao" type="text" placeholder="Descrição da carga" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <textarea id="embalagem_observacoes" name="embalagem_observacoes" placeholder="Embalagem/Observações:"></textarea><br>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label for="anexo">Anexo:</label>
                            <input id="anexo" name="anexo" type="file" accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
                        </div>
                    </div>

                    <h3>Dados do Solicitante</h3>
                    <div class="control-group">
                        <div class="controls">
                            <input id="empresa" name="empresa" type="text" placeholder="Empresa" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="reponsavel" name="reponsavel" type="text" placeholder="Responsável" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="telefone" name="telefone" type="tel" placeholder="Telefone" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input id="email" name="email" type="email" placeholder="E-mail" required>
                        </div>
                    </div>
                    <?= $email_admin; ?>
                    <input id="email_admin" type="hidden" name="email_admin" value="<?= $email_admin; ?>" />
                    <input id="subject" type="hidden" name="subject" value="<?= $subject; ?>" />
                    <button type="submit" id="enviar" class="btn btn-default"><?= (!empty($enviar)) ? $enviar : 'Enviar'; ?></button>
                    <div class="loading">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</section>
<script type="text/javascript" src="<?= JURI::base(true) ?>/modules/mod_quotation_form/assets/js/jquery.mask.min.js"></script>
<script type="text/javascript" src="<?= JURI::base(true) ?>/modules/mod_quotation_form/assets/js/scripts.js"></script>