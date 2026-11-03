<form class="signup-form active" id="form-signup-doador" action="#" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tipo" value="doador">
    <p class="signup-step-label" id="doador-step-label">Etapa 1 de 2: Dados pessoais</p>

    <fieldset class="signup-step" id="doador-step-1">
        <div class="form-group">
            <label for="nome-doador">Nome Completo</label>
            <input type="text" id="nome-doador" name="nome-doador" placeholder="Seu nome completo" maxlength="50"
                required>
        </div>

        <div class="form-group">
            <label for="email-doador">Email</label>
            <input type="email" id="email-doador" name="email-doador" placeholder="seu@email.com" maxlength="50"
                required>
        </div>

        <div class="form-group">
            <label for="telefone-doador">Telefone</label>
            <input type="tel" id="telefone-doador" name="telefone-doador" placeholder="(11) 9XXXX-XXXX"
                oninput="formatarTelefoneDoador()" maxlength="18" required>
        </div>

        <div class="form-group">
            <label for="imagem-doador">Imagem (opcional)</label>
            <input type="file" id="imagem-doador" name="imagem-doador" accept="image/*">
        </div>

        <div class="form-group">
            <label for="senha-doador">Senha</label>
            <input type="password" id="senha-doador" name="senha-doador" placeholder="••••••••" maxlength="20" required>
        </div>

        <div class="form-group">
            <label for="confirmar-senha-doador">Confirmar Senha</label>
            <input type="password" id="confirmar-senha-doador" name="confirmar-senha" placeholder="••••••••"
                maxlength="20" required>
        </div>

        <button type="button" class="btn-signup" id="doador-next-step">Continuar</button>
    </fieldset>

    <fieldset class="signup-step" id="doador-step-2" disabled hidden>
        <div class="form-group">
            <label for="cpf-doador">CPF</label>
            <input type="text" id="cpf-doador" name="cpf-doador" placeholder="XXX.XXX.XXX-XX" oninput="formatarCPF()"
                maxlength="14" required>
        </div>

        <div class="form-group">
            <label for="cep-doador">CEP</label>
            <div class="cep-input-wrapper">
                <input type="text" id="cep-doador" name="cep-doador" placeholder="00000-000" inputmode="numeric"
                    autocomplete="postal-code" maxlength="9" required>
                <button type="button" class="cep-info-button" data-cep-suffix="doador" aria-label="Ver informações do CEP">
                    +
                </button>
            </div>
            <small id="cep-status-doador" aria-live="polite"></small>
        </div>

        <div class="form-group">
            <label for="logradouro-doador">Logradouro</label>
            <input type="text" id="logradouro-doador" name="logradouro-doador" autocomplete="address-line1" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="numero-doador">Número</label>
                <input type="text" id="numero-doador" name="numero-doador" autocomplete="address-line2" required>
            </div>

            <div class="form-group">
                <label for="uf-doador">UF</label>
                <input type="text" id="uf-doador" name="uf-doador" placeholder="SP" maxlength="2" pattern="[A-Za-z]{2}"
                    title="Informe a sigla de dois caracteres do estado" autocomplete="address-level1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="complemento-doador">Complemento (opcional)</label>
            <input type="text" id="complemento-doador" name="complemento-doador">
        </div>

        <div class="form-group">
            <label for="bairro-doador">Bairro</label>
            <input type="text" id="bairro-doador" name="bairro-doador" required>
        </div>

        <div class="form-group">
            <label for="cidade-doador">Cidade</label>
            <input type="text" id="cidade-doador" name="cidade-doador" required>
        </div>

        <div class="form-agreement">
            <label class="checkbox-agreement">
                <input type="checkbox" name="agreement" required>
                Concordo com os termos de serviço
            </label>
        </div>

        <div class="ong-step-actions">
            <button type="button" class="btn-step-back" id="doador-previous-step">Voltar</button>
            <button type="submit" class="btn-signup">Cadastrar como Doador</button>
        </div>
    </fieldset>

    <dialog class="cep-info-modal" id="cep-info-modal-doador" aria-labelledby="cep-info-title-doador">
        <div class="cep-info-modal-header">
            <h2 id="cep-info-title-doador">Informações do CEP</h2>
            <button type="button" class="cep-info-close" aria-label="Fechar informações do CEP">&times;</button>
        </div>
        <p class="cep-info-message" id="cep-info-message-doador" aria-live="polite">Buscando endereço...</p>
        <dl class="cep-info-details" id="cep-info-details-doador" hidden>
            <div><dt>CEP</dt><dd data-cep-info="cep"></dd></div>
            <div><dt>Logradouro</dt><dd data-cep-info="logradouro"></dd></div>
            <div><dt>Bairro</dt><dd data-cep-info="bairro"></dd></div>
            <div><dt>Cidade</dt><dd data-cep-info="cidade"></dd></div>
            <div><dt>UF</dt><dd data-cep-info="uf"></dd></div>
        </dl>
    </dialog>
</form>