<form class="signup-form" id="form-signup-ong" action="#" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tipo" value="ong">
    <p class="signup-step-label" id="ong-step-label">Etapa 1 de 3: Dados da ONG</p>

    <fieldset class="ong-step" id="ong-step-1">
        <div class="form-group">
            <label for="nome-ong">Nome da ONG</label>
            <input type="text" id="nome-ong" name="nome-ong" placeholder="Nome da organização" maxlength="30" required>
        </div>

        <div class="form-group">
            <label for="email-ong">Email</label>
            <input type="email" id="email-ong" name="email-ong" placeholder="contato@ong.com.br" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="telefone-ong">Telefone</label>
            <input type="tel" id="telefone-ong" name="telefone-ong" placeholder="(11) 9XXXX-XXXX" oninput="formatarTelefoneOng()" maxlength="18" required>
        </div>

        <div class="form-group">
            <label for="senha-ong">Senha</label>
            <input type="password" id="senha-ong" name="senha-ong" placeholder="••••••••" maxlength="20" required>
        </div>

        <div class="form-group">
            <label for="confirmar-senha-ong">Confirmar Senha</label>
            <input type="password" id="confirmar-senha-ong" name="confirmar-senha" placeholder="••••••••" maxlength="20" required>
        </div>

        <button type="button" class="btn-signup" id="ong-next-step">Continuar</button>
    </fieldset>

    <fieldset class="ong-step" id="ong-step-2" disabled hidden>
        <div class="form-group">
            <label for="cnpj-ong">CNPJ</label>
            <input type="text" id="cnpj-ong" name="cnpj-ong" placeholder="XX.XXX.XXX/XXXX-XX" oninput="formatarCNPJ()" maxlength="18" required>
        </div>

        <div class="form-group">
            <label for="cep-ong">CEP</label>
            <div class="cep-input-wrapper">
                <input type="text" id="cep-ong" name="cep-ong" placeholder="00000-000" inputmode="numeric" autocomplete="postal-code" maxlength="9" required>
                <button type="button" class="cep-info-button" data-cep-suffix="ong" aria-label="Ver informações do CEP">+</button>
            </div>
            <small id="cep-status-ong" aria-live="polite"></small>
        </div>

        <div class="form-group">
            <label for="logradouro-ong">Logradouro</label>
            <input type="text" id="logradouro-ong" name="logradouro-ong" autocomplete="address-line1" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="numero-ong">Número</label>
                <input type="text" id="numero-ong" name="numero-ong" autocomplete="address-line2" required>
            </div>

            <div class="form-group">
                <label for="uf-ong">UF</label>
                <input type="text" id="uf-ong" name="uf-ong" placeholder="SP" maxlength="2" pattern="[A-Za-z]{2}" title="Informe a sigla de dois caracteres do estado" autocomplete="address-level1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="complemento-ong">Complemento (opcional)</label>
            <input type="text" id="complemento-ong" name="complemento-ong">
        </div>

        <div class="form-group">
            <label for="bairro-ong">Bairro</label>
            <input type="text" id="bairro-ong" name="bairro-ong" required>
        </div>

        <div class="form-group">
            <label for="cidade-ong">Cidade</label>
            <input type="text" id="cidade-ong" name="cidade-ong" autocomplete="address-level2" required>
        </div>

        <div class="form-group">
            <label for="imagem-ong">Imagem</label>
            <input type="file" id="imagem-ong" name="imagem-ong" accept="image/*" required>
        </div>

        <div class="ong-step-actions">
            <button type="button" class="btn-step-back" id="ong-previous-step">Voltar</button>
            <button type="button" class="btn-signup" id="ong-next-step-2">Continuar</button>
        </div>
    </fieldset>

    <fieldset class="ong-step" id="ong-step-3" disabled hidden>
        <div class="form-group">
            <label for="distancia-ong">Distância (km)</label>
            <input type="number" id="distancia-ong" name="distancia-ong" min="0" step="0.1" placeholder="Ex.: 3" required>
        </div>

        <div class="form-group">
            <label for="documento-ong">Documento (opcional)</label>
            <input type="text" id="documento-ong" name="documento" placeholder="Comprovante ou documento adicional">
        </div>

        <div class="form-group">
            <label for="doacoes-ong">Doações aceitas</label>
            <textarea id="doacoes-ong" name="doacoes-ong" placeholder="Informe os tipos de doação aceitos" required></textarea>
        </div>

        <div class="form-group">
            <label for="sobre-ong">Sobre a ONG</label>
            <textarea id="sobre-ong" name="sobre-ong" required></textarea>
        </div>

        <div class="form-group">
            <label for="missao-ong">Missão</label>
            <textarea id="missao-ong" name="missao-ong" required></textarea>
        </div>

        <div class="form-group">
            <label for="projetos-ong">Projetos ativos</label>
            <textarea id="projetos-ong" name="projetos-ong" placeholder="Informe os projetos da ONG" required></textarea>
        </div>

        <div class="form-agreement">
            <label class="checkbox-agreement">
                <input type="checkbox" name="agreement" required>
                Concordo com os termos de serviço
            </label>
        </div>

        <div class="ong-step-actions">
            <button type="button" class="btn-step-back" id="ong-previous-step-2">Voltar</button>
            <button type="submit" class="btn-signup">Cadastrar como ONG</button>
        </div>
    </fieldset>

    <dialog class="cep-info-modal" id="cep-info-modal-ong" aria-labelledby="cep-info-title-ong">
        <div class="cep-info-modal-header">
            <h2 id="cep-info-title-ong">Informações do CEP</h2>
            <button type="button" class="cep-info-close" aria-label="Fechar informações do CEP">&times;</button>
        </div>
        <p class="cep-info-message" id="cep-info-message-ong" aria-live="polite">Buscando endereço...</p>
        <dl class="cep-info-details" id="cep-info-details-ong" hidden>
            <div><dt>CEP</dt><dd data-cep-info="cep"></dd></div>
            <div><dt>Logradouro</dt><dd data-cep-info="logradouro"></dd></div>
            <div><dt>Bairro</dt><dd data-cep-info="bairro"></dd></div>
            <div><dt>Cidade</dt><dd data-cep-info="cidade"></dd></div>
            <div><dt>UF</dt><dd data-cep-info="uf"></dd></div>
        </dl>
    </dialog>
</form>
