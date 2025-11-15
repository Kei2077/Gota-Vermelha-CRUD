<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Cadastre-se na plataforma Gota Vermelha">
  <title>Cadastro – Gota Vermelha</title>
  <style>
    :root {
      --cor-primaria: #E2230A;
      --cor-primaria-hover: #C41E0A;
      --cor-secundaria: #880202;
      --cor-fundo: #F9F9F9;
      --cor-card: #FFFFFF;
      --cor-texto: #2D2D2D;
      --cor-texto-secundario: #6B7280;
      --cor-borda: #E5E7EB;
      --cor-sucesso: #10B981;
      --cor-erro: #EF4444;
      --cor-alerta: #F59E0B;
      --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --sombra-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --raio-borda: 12px;
      --transicao: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: linear-gradient(135deg, var(--cor-fundo) 0%, #F3F4F6 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }

    .cadastro-card {
      background: var(--cor-card);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      width: 100%;
      max-width: 480px;
      padding: 48px 32px;
      transition: var(--transicao);
    }

    .cadastro-card:hover {
      box-shadow: var(--sombra-hover);
    }

    .cadastro-card h1 {
      color: var(--cor-primaria);
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
      text-align: center;
    }

    .subtitle {
      color: var(--cor-texto-secundario);
      font-size: 15px;
      text-align: center;
      margin-bottom: 32px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: var(--cor-texto);
      margin-bottom: 8px;
    }

    input, select {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid var(--cor-borda);
      border-radius: 8px;
      font-size: 16px;
      transition: var(--transicao);
      background-color: #F9FAFB;
      font-family: inherit;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--cor-primaria);
      background-color: var(--cor-card);
      box-shadow: 0 0 0 3px rgba(226, 35, 10, 0.1);
    }

    input::placeholder {
      color: #9CA3AF;
    }

    .error {
      border-color: var(--cor-erro) !important;
    }

    .error-message {
      color: var(--cor-erro);
      font-size: 13px;
      margin-top: 6px;
      display: none;
    }

    .error-message.show {
      display: block;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(to bottom, var(--cor-primaria), var(--cor-primaria-hover));
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transicao);
      margin-top: 8px;
    }

    button:hover {
      background: linear-gradient(to bottom, var(--cor-primaria-hover), var(--cor-secundaria));
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(226, 35, 10, 0.25);
    }

    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .links {
      margin-top: 32px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    a {
      color: var(--cor-primaria);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      transition: var(--transicao);
    }

    a:hover {
      background-color: rgba(226, 35, 10, 0.05);
    }

    .btn-outline {
      border: 2px solid var(--cor-primaria);
      color: var(--cor-primaria);
      font-weight: 600;
    }

    .btn-outline:hover {
      background-color: var(--cor-primaria);
      color: #fff;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: none;
    }

    .alert.show {
      display: block;
    }

    .alert-success {
      background-color: #D1FAE5;
      color: #065F46;
      border: 1px solid var(--cor-sucesso);
    }

    .alert-error {
      background-color: #FEE2E2;
      color: #991B1B;
      border: 1px solid var(--cor-erro);
    }

    /* NOVOS ESTILOS PARA O INDICADOR DE SENHA */
    .strength-meter {
      height: 6px;
      background-color: var(--cor-borda);
      border-radius: 3px;
      margin-top: 8px;
      overflow: hidden;
      display: none;
      transition: var(--transicao);
    }

    .strength-meter.show {
      display: block;
    }

    .strength-meter-fill {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease, background-color 0.3s ease;
    }

    .strength-meter-fill.weak {
      width: 33%;
      background-color: var(--cor-erro);
    }

    .strength-meter-fill.medium {
      width: 66%;
      background-color: var(--cor-alerta);
    }

    .strength-meter-fill.strong {
      width: 100%;
      background-color: var(--cor-sucesso);
    }

    .strength-text {
      font-size: 13px;
      margin-top: 6px;
      font-weight: 500;
      display: none;
    }

    .strength-text.show {
      display: block;
    }

    .strength-text.weak {
      color: var(--cor-erro);
    }

    .strength-text.medium {
      color: var(--cor-alerta);
    }

    .strength-text.strong {
      color: var(--cor-sucesso);
    }

    .password-requirements {
      margin-top: 10px;
      font-size: 13px;
      color: var(--cor-texto-secundario);
      display: none;
    }

    .password-requirements.show {
      display: block;
    }

    .requirement-item {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 4px;
    }

    .requirement-icon {
      font-size: 14px;
      font-weight: bold;
    }

    .requirement-icon.valid {
      color: var(--cor-sucesso);
    }

    .requirement-icon.invalid {
      color: var(--cor-texto-secundario);
    }

    .requirement-text {
      font-size: 13px;
    }

    .requirement-text.valid {
      color: var(--cor-texto);
      font-weight: 500;
    }

    @media (max-width: 480px) {
      .cadastro-card {
        padding: 32px 24px;
      }
      
      .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <main class="cadastro-card">
    <h1>Criar Conta</h1>
    <p class="subtitle">Preencha seus dados para se cadastrar</p>
    
    <div id="alerta" class="alert"></div>

    <form id="formCadastro" action="processa_cadastro.php" method="POST">
      <div class="form-group">
        <label for="nome">Nome Completo *</label>
        <input type="text" id="nome" name="nome" required placeholder="João da Silva">
        <span class="error-message" id="erro-nome"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="cpf">CPF *</label>
          <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" maxlength="14">
          <span class="error-message" id="erro-cpf"></span>
        </div>

        <div class="form-group">
          <label for="data_nascimento">Data de Nascimento *</label>
          <input type="date" id="data_nascimento" name="data_nascimento" required>
          <span class="error-message" id="erro-data"></span>
        </div>
      </div>

      <div class="form-group">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" required placeholder="seu@email.com">
        <span class="error-message" id="erro-email"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="senha">Senha *</label>
          <input type="password" id="senha" name="senha" required placeholder="Crie uma senha forte">
          <span class="error-message" id="erro-senha"></span>
   
          <div id="strength-meter" class="strength-meter">
            <div id="strength-meter-fill" class="strength-meter-fill"></div>
          </div>
          <div id="strength-text" class="strength-text"></div>
    
          <div id="password-requirements" class="password-requirements">
            <div class="requirement-item">
              <span class="requirement-icon invalid" id="icon-length">○</span>
              <span class="requirement-text" id="text-length">Mínimo 8 caracteres</span>
            </div>
            <div class="requirement-item">
              <span class="requirement-icon invalid" id="icon-uppercase">○</span>
              <span class="requirement-text" id="text-uppercase">Uma letra maiúscula</span>
            </div>
            <div class="requirement-item">
              <span class="requirement-icon invalid" id="icon-lowercase">○</span>
              <span class="requirement-text" id="text-lowercase">Uma letra minúscula</span>
            </div>
            <div class="requirement-item">
              <span class="requirement-icon invalid" id="icon-number">○</span>
              <span class="requirement-text" id="text-number">Um número</span>
            </div>
            <div class="requirement-item">
              <span class="requirement-icon invalid" id="icon-special">○</span>
              <span class="requirement-text" id="text-special">Um caractere especial (!@#$%&*)</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="confirma_senha">Confirmar Senha *</label>
          <input type="password" id="confirma_senha" name="confirma_senha" required placeholder="Repita a senha">
          <span class="error-message" id="erro-confirma"></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="tipo_sanguineo">Tipo Sanguíneo *</label>
          <select id="tipo_sanguineo" name="tipo_sanguineo" required>
            <option value="">Selecione</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="AB">AB</option>
            <option value="O">O</option>
          </select>
        </div>

        <div class="form-group">
          <label for="fator_rh">Fator RH *</label>
          <select id="fator_rh" name="fator_rh" required>
            <option value="">Selecione</option>
            <option value="POSITIVO">POSITIVO (+)</option>
            <option value="NEGATIVO">NEGATIVO (-)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="telefone">Telefone *</label>
        <input type="tel" id="telefone" name="telefone" required placeholder="(00) 00000-0000" maxlength="15">
        <span class="error-message" id="erro-telefone"></span>
      </div>

      <div class="form-group">
        <label for="endereco">Endereço Completo *</label>
        <input type="text" id="endereco" name="endereco" required placeholder="Rua, número, bairro, cidade - UF">
        <span class="error-message" id="erro-endereco"></span>
      </div>

      <button type="submit" id="btnSubmit">Cadastrar</button>
    </form>

    <div class="links">
      <p style="color: var(--cor-texto-secundario); font-size: 14px; text-align: center;">
        Já tem uma conta? <a href="login.php">Faça login</a>
      </p>
    </div>
  </main>

  <script>
   
    document.getElementById('cpf').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
      } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
      } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
      }
      e.target.value = value;
    });

    document.getElementById('telefone').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 10) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      } else if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
      } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{4})/, '($1) $2');
      }
      e.target.value = value;
    });

    const form = document.getElementById('formCadastro');
    const campos = form.querySelectorAll('input[required], select[required]');

    campos.forEach(campo => {
      campo.addEventListener('blur', () => validarCampo(campo));
    });

    function validarCampo(campo) {
      const nomeCampo = campo.name;
      const erroElement = document.getElementById(`erro-${nomeCampo.replace('_', '-')}`);
      
      campo.classList.remove('error');
      if (erroElement) erroElement.classList.remove('show');

     
      if (nomeCampo === 'cpf' && campo.value) {
        if (!validarCPF(campo.value)) {
          mostrarErro(campo, erroElement, 'CPF inválido');
          return false;
        }
      }

      if (nomeCampo === 'email' && campo.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(campo.value)) {
          mostrarErro(campo, erroElement, 'E-mail inválido');
          return false;
        }
      }

      return true;
    }

    function mostrarErro(campo, erroElement, mensagem) {
      campo.classList.add('error');
      if (erroElement) {
        erroElement.textContent = mensagem;
        erroElement.classList.add('show');
      }
    }

    function validarCPF(cpf) {
      cpf = cpf.replace(/\D/g, '');
      if (cpf.length !== 11) return false;
      
      if (/^(\d)\1{10}$/.test(cpf)) return false;
      
      let soma = 0;
      for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
      }
      let resto = 11 - (soma % 11);
      let digito = resto > 9 ? 0 : resto;
      if (parseInt(cpf.charAt(9)) !== digito) return false;
      
      soma = 0;
      for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
      }
      resto = 11 - (soma % 11);
      digito = resto > 9 ? 0 : resto;
      
      return parseInt(cpf.charAt(10)) === digito;
    }

    const senhaInput = document.getElementById('senha');
    const strengthMeter = document.getElementById('strength-meter');
    const strengthMeterFill = document.getElementById('strength-meter-fill');
    const strengthText = document.getElementById('strength-text');
    const requirements = document.getElementById('password-requirements');

    senhaInput.addEventListener('focus', function() {
      requirements.classList.add('show');
    });

    senhaInput.addEventListener('input', function() {
      const senha = this.value;
      verificarForcaSenha(senha);
    });

    senhaInput.addEventListener('blur', function() {

      setTimeout(() => {
        if (this.value.length === 0) {
          requirements.classList.remove('show');
          strengthMeter.classList.remove('show');
          strengthText.classList.remove('show');
        }
      }, 200);
    });

    function verificarForcaSenha(senha) {
      
      const requisitos = {
        length: senha.length >= 8,
        uppercase: /[A-Z]/.test(senha),
        lowercase: /[a-z]/.test(senha),
        number: /\d/.test(senha),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(senha)
      };

    
      Object.keys(requisitos).forEach(key => {
        const icon = document.getElementById(`icon-${key}`);
        const text = document.getElementById(`text-${key}`);
        
        if (requisitos[key]) {
          icon.textContent = '✓';
          icon.classList.add('valid');
          icon.classList.remove('invalid');
          text.classList.add('valid');
        } else {
          icon.textContent = '○';
          icon.classList.add('invalid');
          icon.classList.remove('valid');
          text.classList.remove('valid');
        }
      });

      
      const pontos = Object.values(requisitos).filter(Boolean).length;
      
      
      strengthMeter.classList.add('show');
      strengthText.classList.add('show');
      
      if (pontos <= 2) {
        strengthMeterFill.className = 'strength-meter-fill weak';
        strengthText.textContent = 'Força: Fraca';
        strengthText.className = 'strength-text show weak';
      } else if (pontos <= 4) {
        strengthMeterFill.className = 'strength-meter-fill medium';
        strengthText.textContent = 'Força: Média';
        strengthText.className = 'strength-text show medium';
      } else {
        strengthMeterFill.className = 'strength-meter-fill strong';
        strengthText.textContent = 'Força: Forte';
        strengthText.className = 'strength-text show strong';
      }

     
      if (senha.length > 0 && pontos < 5) {
        senhaInput.classList.add('error');
        document.getElementById('erro-senha').textContent = 'Atenda a todos os requisitos de segurança';
        document.getElementById('erro-senha').classList.add('show');
      } else {
        senhaInput.classList.remove('error');
        document.getElementById('erro-senha').classList.remove('show');
      }
    }

    
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      let valido = true;
      
     
      campos.forEach(campo => {
        if (!validarCampo(campo)) valido = false;
      });

      
      const senha = senhaInput.value;
      const requisitos = {
        length: senha.length >= 8,
        uppercase: /[A-Z]/.test(senha),
        lowercase: /[a-z]/.test(senha),
        number: /\d/.test(senha),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(senha)
      };
      const pontos = Object.values(requisitos).filter(Boolean).length;
      
      if (pontos < 5) {
        mostrarErro(senhaInput, document.getElementById('erro-senha'), 'Atenda a todos os requisitos de segurança');
        valido = false;
      }

      
      const confirmaSenha = document.getElementById('confirma_senha');
      if (confirmaSenha.value !== senha) {
        mostrarErro(confirmaSenha, document.getElementById('erro-confirma'), 'As senhas não coincidem');
        valido = false;
      }

      if (valido) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.textContent = 'Cadastrando...';
        this.submit();
      }
    });
  </script>
</body>
</html>