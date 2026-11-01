<form class="signup-form active" id="form-signup-doador" action="#" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="tipo" value="doador">
    <div class="form-group">
        <label for="nome-doador">Nome Completo</label>
        <input type="text" id="nome-doador" name="nome-doador" placeholder="Seu nome completo" maxlength="50"required>
    </div>

    <div class="form-group">
        <label for="email-doador">Email</label>
        <input type="email" id="email-doador" name="email-doador" placeholder="seu@email.com" maxlength="50" required>
    </div>

    <div class="form-group">
        <label for="telefone-doador">Telefone</label>
        <input type="tel" id="telefone-doador" name="telefone-doador" placeholder="(11) 9XXXX-XXXX" oninput="formatarTelefoneDoador()" maxlength="18" required>
    </div>

    <div class="form-group">
        <label for="endereco-doador">Endereço</label>
        <input type="text" id="endereco-doador" name="endereco-doador" placeholder="Rua, número, complemento" required>
    </div>

    <div class="form-group">
        <label for="cpf-doador">CPF</label>
        <input type="text" id="cpf-doador" name="cpf-doador" placeholder="XXX.XXX.XXX-XX" oninput="formatarCPF()" maxlength="14" required>
    </div>

    <div class="form-group">
        <label for="imagem-doador">Imagem</label>
        <input type="file" id="imagem-doador" name="imagem-doador" accept="image/*">
    </div>

    <div class="form-group">
        <label for="senha-doador">Senha</label>
        <input type="password" id="senha-doador" name="senha-doador" placeholder="••••••••" maxlength="20" required>
    </div>

    <div class="form-group">
        <label for="confirmar-senha-doador">Confirmar Senha</label>
        <input type="password" id="confirmar-senha-doador" name="confirmar-senha" placeholder="••••••••" maxlength="20" required>
    </div>

    <div class="form-agreement">
        <label class="checkbox-agreement">
            <input type="checkbox" name="agreement" required>
            Concordo com os termos de serviço
        </label>
    </div>

    <button type="submit" class="btn-signup">Cadastrar como Doador</button>
</form>