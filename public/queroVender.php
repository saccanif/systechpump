<?php
require_once '../config/connection.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quero Vender | Systech Pump</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./css/vender1.css">
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0e47a1 0%, #0a3570 50%, #3b81ee 100%);
      min-height: 100vh;
      padding: 2rem 1rem;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(59, 129, 238, 0.15) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    .container-vender {
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .header-vender {
      text-align: center;
      color: white;
      margin-bottom: 4rem;
      padding: 2rem 0;
    }

    .header-vender h1 {
      font-size: 3rem;
      margin-bottom: 1rem;
      text-shadow: 0 4px 20px rgba(0,0,0,0.3);
      font-weight: 800;
      background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .header-vender h1 i {
      margin-right: 1rem;
      color: white;
      -webkit-text-fill-color: white;
    }

    .header-vender p {
      font-size: 1.3rem;
      opacity: 0.95;
      font-weight: 300;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
      color: white;
    }

    .opcoes {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 3rem;
      margin-bottom: 4rem;
    }

    .opcao {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 3rem 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
      border: 2px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
    }

    .opcao::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(14, 71, 161, 0.1), transparent);
      transition: left 0.5s;
    }

    .opcao:hover::before {
      left: 100%;
    }

    .opcao:hover {
      transform: translateY(-15px) scale(1.03);
      box-shadow: 0 25px 60px rgba(0,0,0,0.3);
      border-color: rgba(255, 255, 255, 0.5);
    }

    .opcao img {
      width: 180px;
      height: 180px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 1.5rem;
      border: 5px solid rgba(14, 71, 161, 0.1);
      transition: all 0.4s ease;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .opcao:hover img {
      transform: scale(1.1) rotate(5deg);
      border-color: rgba(14, 71, 161, 0.3);
      box-shadow: 0 15px 40px rgba(14, 71, 161, 0.3);
    }

    .opcao button {
      background: linear-gradient(135deg, #0e47a1 0%, #3b81ee 100%);
      color: white;
      border: none;
      padding: 1.2rem 2.5rem;
      border-radius: 12px;
      font-size: 1.2rem;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      transition: all 0.3s;
      box-shadow: 0 5px 15px rgba(14, 71, 161, 0.3);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .opcao button:hover {
      background: linear-gradient(135deg, #3b81ee 0%, #0e47a1 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(14, 71, 161, 0.4);
    }

    .opcao button i {
      margin-right: 0.5rem;
    }
    #formulario-container {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 3.5rem;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      display: none;
      max-width: 850px;
      margin: 0 auto;
      border: 2px solid rgba(255, 255, 255, 0.3);
      animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    #formulario-container.active {
      display: block;
    }

    #formulario-titulo {
      font-size: 2rem;
      margin-bottom: 2rem;
      color: #0e47a1;
      font-weight: 700;
      text-align: center;
      padding-bottom: 1rem;
      border-bottom: 3px solid #0e47a1;
    }

    .form-group {
      margin-bottom: 1.75rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.75rem;
      font-weight: 600;
      color: #0e47a1;
      font-size: 1.05rem;
    }

    .form-group label i {
      margin-right: 0.5rem;
      color: #3b81ee;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 1rem 1.25rem;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s;
      font-family: 'Poppins', sans-serif;
      background: white;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #0e47a1;
      box-shadow: 0 0 0 4px rgba(14, 70, 161, 0.1);
      transform: translateY(-2px);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .btn-submit {
      background: linear-gradient(135deg, #0e47a1 0%, #3b81ee 100%);
      color: white;
      border: none;
      padding: 1.25rem 2.5rem;
      border-radius: 12px;
      font-size: 1.2rem;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      transition: all 0.3s;
      margin-top: 1.5rem;
      box-shadow: 0 5px 20px rgba(14, 71, 161, 0.3);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, #3b81ee 0%, #0e47a1 100%);
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(14, 71, 161, 0.4);
    }

    .btn-submit i {
      margin-right: 0.5rem;
    }

    .btn-voltar {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      border: none;
      padding: 0.875rem 1.75rem;
      border-radius: 10px;
      cursor: pointer;
      margin-bottom: 1.5rem;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .btn-voltar:hover {
      background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
      transform: translateX(-5px);
    }

    .btn-voltar i {
      margin-right: 0.5rem;
    }

    #mensagem-sucesso {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 4rem 3rem;
      text-align: center;
      display: none;
      max-width: 650px;
      margin: 0 auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      border: 2px solid rgba(40, 167, 69, 0.3);
      animation: slideIn 0.5s ease-out;
    }

    #mensagem-sucesso.active {
      display: block;
    }

    #mensagem-sucesso h3 {
      color: #0e47a1;
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      font-weight: 700;
    }

    #mensagem-sucesso p {
      color: #555;
      line-height: 1.8;
      font-size: 1.1rem;
    }

    @media (max-width: 768px) {
      .header-vender h1 {
        font-size: 2rem;
      }

      .header-vender p {
        font-size: 1.1rem;
      }

      .opcoes {
        grid-template-columns: 1fr;
        gap: 2rem;
      }

      .opcao {
        padding: 2rem 1.5rem;
      }

      .opcao img {
        width: 150px;
        height: 150px;
      }

      #formulario-container {
        padding: 2rem 1.5rem;
      }

      #formulario-titulo {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container-vender">
    <div class="header-vender">
      <h1><i class="fas fa-handshake"></i> Quero Vender Produtos Pump</h1>
      <p>Escolha sua opção e preencha o formulário</p>
    </div>

    <div class="opcoes" id="opcoes">
      <div class="opcao" onclick="mostrarFormulario('lojista')">
        <img src="./imgs/others/loja.jpg" alt="Lojista" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27150%27%3E%3Crect fill=%27%230e47a1%27 width=%27150%27 height=%27150%27/%3E%3Ctext fill=%27white%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-size=%2740%27%3E🏪%3C/text%3E%3C/svg%3E'">
        <button><i class="fas fa-store"></i> Sou Lojista</button>
      </div>
      <div class="opcao" onclick="mostrarFormulario('representante')">
        <img src="./imgs/others/representante.jpg" alt="Representante" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27150%27%3E%3Crect fill=%27%230e47a1%27 width=%27150%27 height=%27150%27/%3E%3Ctext fill=%27white%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-size=%2740%27%3E👔%3C/text%3E%3C/svg%3E'">
        <button><i class="fas fa-user-tie"></i> Sou Representante</button>
      </div>
    </div>

    <div id="formulario-container">
      <button class="btn-voltar" onclick="voltarOpcoes()"><i class="fas fa-arrow-left"></i> Voltar</button>
      <h2 id="formulario-titulo" style="color: #0e47a1; margin-bottom: 2rem;"></h2>
      <form id="formSolicitacao" onsubmit="enviarFormulario(event)">
        <input type="hidden" id="tipoSolicitacao" name="tipoSolicitacao">
        
        <div class="form-group">
          <label for="nome"><i class="fas fa-user"></i> Nome Completo *</label>
          <input type="text" id="nome" name="nome" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> E-mail *</label>
            <input type="email" id="email" name="email" required>
          </div>
          <div class="form-group">
            <label for="telefone"><i class="fas fa-phone"></i> Telefone *</label>
            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
          </div>
        </div>

        <div class="form-group">
          <label for="cnpj"><i class="fas fa-id-card"></i> CNPJ *</label>
          <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="estado"><i class="fas fa-map"></i> Estado *</label>
            <select id="estado" name="estado" required onchange="carregarCidades()">
              <option value="">Selecione o estado</option>
              <?php
              $con = conectarBD();
              $sql = "SELECT * FROM estado ORDER BY nomeEstado";
              $res = mysqli_query($con, $sql);
              while ($reg = mysqli_fetch_assoc($res)) {
                echo "<option value='{$reg['idEstado']}'>{$reg['nomeEstado']}</option>";
              }
              mysqli_close($con);
              ?>
            </select>
          </div>
          <div class="form-group">
            <label for="cidade"><i class="fas fa-city"></i> Cidade *</label>
            <select id="cidade" name="cidade" required disabled>
              <option value="">Selecione a cidade</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="mensagem"><i class="fas fa-comment"></i> Mensagem *</label>
          <textarea id="mensagem" name="mensagem" rows="5" placeholder="Conte-nos sobre você e seu interesse em vender nossos produtos..." required></textarea>
        </div>

        <button type="submit" class="btn-submit">
          <i class="fas fa-paper-plane"></i> Enviar Solicitação
        </button>
      </form>
    </div>

    <div id="mensagem-sucesso">
      <div style="font-size: 60px; color: #28a745; margin-bottom: 1rem;">✅</div>
      <h3 style="color: #0e47a1; margin-bottom: 1rem;">Solicitação Enviada com Sucesso!</h3>
      <p style="color: #666; line-height: 1.6;">
        Agradecemos seu interesse em representar ou vender nossos produtos.<br>
        Em breve, nossa equipe entrará em contato com você pelo e-mail ou telefone informado.<br><br>
        <strong>💙 Obrigado por escolher a Systech Pump!</strong>
      </p>
    </div>
  </div>

  <script>
    let tipoSelecionado = '';

    function mostrarFormulario(tipo) {
      tipoSelecionado = tipo;
      document.getElementById('opcoes').style.display = 'none';
      document.getElementById('formulario-container').classList.add('active');
      document.getElementById('tipoSolicitacao').value = tipo;
      
      const titulo = document.getElementById('formulario-titulo');
      titulo.innerHTML = tipo === 'lojista' 
        ? '<i class="fas fa-store"></i> Cadastro de Lojista'
        : '<i class="fas fa-user-tie"></i> Cadastro de Representante';
    }

    function voltarOpcoes() {
      document.getElementById('opcoes').style.display = 'grid';
      document.getElementById('formulario-container').classList.remove('active');
      document.getElementById('mensagem-sucesso').classList.remove('active');
      document.getElementById('formSolicitacao').reset();
      document.getElementById('cidade').disabled = true;
      document.getElementById('cidade').innerHTML = '<option value="">Selecione a cidade</option>';
    }

    function carregarCidades() {
      const estadoId = document.getElementById('estado').value;
      const cidadeSelect = document.getElementById('cidade');
      
      if (!estadoId) {
        cidadeSelect.disabled = true;
        cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>';
        return;
      }

      cidadeSelect.disabled = true;
      cidadeSelect.innerHTML = '<option value="">Carregando...</option>';

      fetch(`./get_cidades.php?estado=${estadoId}`)
        .then(resp => resp.text())
        .then(html => {
          cidadeSelect.innerHTML = html;
          cidadeSelect.disabled = false;
        })
        .catch(error => {
          console.error('Erro:', error);
          cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
        });
    }

    function enviarFormulario(event) {
      event.preventDefault();
      
      const formData = new FormData(event.target);
      formData.append('tipoSolicitacao', tipoSelecionado);

      fetch('./processarSolicitacao.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('formulario-container').classList.remove('active');
          document.getElementById('mensagem-sucesso').classList.add('active');
        } else {
          alert('Erro ao enviar solicitação: ' + (data.message || 'Tente novamente'));
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar solicitação. Tente novamente.');
      });
    }

    // Máscara para telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        if (value.length < 14) {
          value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        e.target.value = value;
      }
    });

    // Máscara para CNPJ
    document.getElementById('cnpj').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length <= 14) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        e.target.value = value;
      }
    });
  </script>
</body>
</html>



