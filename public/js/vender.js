function mostrarFormulario(tipo) {
    // Esconde os botões de escolha
    document.querySelector('.opcoes').style.display = 'none';
      
    const formulario = document.getElementById("formulario-container");
    const titulo = document.getElementById("formulario-titulo");

    // Altera o título com base na escolha
    titulo.innerText = tipo === "lojista"
    ? "Cadastro de lojista"
    : "Cadastro de representante";

    formulario.style.display = "block";  // Exibe o formulário
    document.getElementById("mensagem-sucesso").style.display = "none";
}

function enviarFormulario(event) {
    event.preventDefault();
    document.getElementById("formulario-container").style.display = "none";
    document.getElementById("mensagem-sucesso").style.display = "block";
}