document.getElementById('budgetForm').addEventListener('submit', function(e) {
    let isValid = true;

    // Lista atualizada com todos os campos obrigatórios
    const fields = [
        { id: 'nome', errorId: 'error-nome' },
        { id: 'telefone', errorId: 'error-telefone' },
        { id: 'email', errorId: 'error-email' },
        { id: 'cpf', errorId: 'error-cpf' },
        { id: 'tipo_residencia', errorId: 'error-tipo_residencia' },
        { id: 'cep', errorId: 'error-cep' },
        { id: 'cidade', errorId: 'error-cidade' },
        { id: 'logradouro', errorId: 'error-logradouro' },
        { id: 'numero', errorId: 'error-numero' },
        { id: 'bairro', errorId: 'error-bairro' },
        { id: 'estado', errorId: 'error-estado' },
        { id: 'tipo_solicitacao', errorId: 'error-tipo_solicitacao' }
    ];

    fields.forEach(field => {
        const input = document.getElementById(field.id);
        const error = document.getElementById(field.errorId);

        if (!input.value.trim()) {
            input.classList.add('error');
            if (error) error.style.display = 'block';
            isValid = false;
        } else {
            input.classList.remove('error');
            if (error) error.style.display = 'none';
        }
    });

    // Validação simples de email
    const emailInput = document.getElementById('email');
    if (emailInput.value.trim() && !emailInput.value.includes('@')) {
        emailInput.classList.add('error');
        document.getElementById('error-email').style.display = 'block';
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault(); // Impede o envio se houver erro
    } else {
        // Trava o botão para evitar cliques duplos e duplicidade de e-mails
        const btnSubmit = this.querySelector('.btn-submit');
        btnSubmit.disabled = true;
        btnSubmit.innerText = 'Enviando...';
    }
});