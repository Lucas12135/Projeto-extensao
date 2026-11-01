<form class="signup-form" id="form-signup-ong" action="#" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tipo" value="ong">
    <p class="signup-step-label" id="ong-step-label">Etapa 1 de 2: Dados da ONG</p>

    <fieldset class="ong-step" id="ong-step-1">
        <div class="form-group">
            <label for="nome-ong">Nome da ONG</label>
            <input type="text" id="nome-ong" name="nome-ong" placeholder="Nome da organização" maxlength="30" required>
        </div>

        <div class="form-group">
            <label for="cnpj-ong">CNPJ</label>
            <input type="text" id="cnpj-ong" name="cnpj-ong" placeholder="XX.XXX.XXX/XXXX-XX" oninput="formatarCNPJ()" maxlength="18" required>
        </div>

        <div class="form-group">
            <label for="documento-ong">Documento (opcional)</label>
            <input type="text" id="documento-ong" name="documento" placeholder="Comprovante ou documento adicional">
        </div>

        <div class="form-group">
            <label for="telefone-ong">Telefone</label>
            <input type="tel" id="telefone-ong" name="telefone-ong" placeholder="(11) 9XXXX-XXXX" oninput="formatarTelefoneOng()" maxlength="18" required>
        </div>

        <div class="form-group">
            <label for="email-ong">Email</label>
            <input type="email" id="email-ong" name="email-ong" placeholder="contato@ong.com.br" maxlength="50" required>
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
            <label for="distancia-ong">Distância (km)</label>
            <input type="number" id="distancia-ong" name="distancia-ong" min="0" step="0.1" placeholder="Ex.: 3"
                required>
        </div>

        <div class="form-group">
            <label for="localizacao-ong">Localização</label>
            <input type="text" id="localizacao-ong" name="localizacao-ong" placeholder="Bairro e cidade" required>
        </div>

        <div class="form-group">
            <label for="doacoes-ong">Doações aceitas</label>
            <textarea id="doacoes-ong" name="doacoes-ong" placeholder="Informe os tipos de doação aceitos"
                required></textarea>
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
            <textarea id="projetos-ong" name="projetos-ong" placeholder="Informe os projetos da ONG"
                required></textarea>
        </div>

        <div class="form-group">
            <label for="imagem-ong">Imagem</label>
            <input type="file" id="imagem-ong" name="imagem-ong" accept="image/*">
        </div>

        <div class="form-agreement">
            <label class="checkbox-agreement">
                <input type="checkbox" name="agreement" required>
                Concordo com os termos de serviço
            </label>
        </div>

        <div class="ong-step-actions">
            <button type="button" class="btn-step-back" id="ong-previous-step">Voltar</button>
            <button type="submit" class="btn-signup">Cadastrar como ONG</button>
        </div>
    </fieldset>
</form>