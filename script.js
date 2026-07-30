document.getElementById('budgetForm').addEventListener('submit', function(e) {
    let isValid = true;

    // Lista com todos os campos obrigatórios do formulário
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

    // Validação avançada de e-mail (Exige estrutura com @ e domínio)
    const emailInput = document.getElementById('email');
    const errorEmail = document.getElementById('error-email');
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (emailInput.value.trim() && !regexEmail.test(emailInput.value.trim())) {
        emailInput.classList.add('error');
        if (errorEmail) {
            errorEmail.innerText = "Digite um e-mail válido (ex: seu@email.com)";
            errorEmail.style.display = 'block';
        }
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault(); // Impede o envio se houver algum erro
    } else {
        // Trava o botão para evitar cliques duplos e duplicidade de e-mails
        const btnSubmit = this.querySelector('.btn-submit');
        btnSubmit.disabled = true;
        btnSubmit.innerText = 'Enviando...';
    }
});

// Verifica se a URL contém "?status=sucesso" após o envio
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'sucesso') {
        const alertSuccess = document.getElementById('alertSuccess');
        if (alertSuccess) {
            alertSuccess.style.display = 'block';
            
            // Rola a página suavemente até o formulário para o usuário ver a mensagem
            document.getElementById('orcamento').scrollIntoView({ behavior: 'smooth' });
    
            // Remove o "?status=sucesso" da URL de forma limpa (sem recarregar a página)
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);

            // Some a mensagem automaticamente após 6 segundos
            setTimeout(() => {
                alertSuccess.style.display = 'none';
            }, 6000);
        }
    }
});

// ==========================================
// MÁSCARAS DE ENTRADA CORRIGIDAS (CPF, Telefone, CEP)
// ==========================================

// Máscara de CPF: máximo 11 números -> 000.000.000-00
const cpfInput = document.getElementById('cpf');
if (cpfInput) {
    cpfInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
        value = value.substring(0, 11); // Trava estritamente em 11 dígitos numéricos
        
        // Aplica a formatação
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        
        e.target.value = value;
    });
}

// Máscara de Telefone: máximo 11 números -> (00) 00000-0000
const telefoneInput = document.getElementById('telefone');
if (telefoneInput) {
    telefoneInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
        value = value.substring(0, 11); // Trava estritamente em 11 dígitos numéricos
        
        // Aplica a formatação
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        
        e.target.value = value;
    });
}

// Máscara de CEP: máximo 8 números -> 00000-000
const cepInput = document.getElementById('cep');
if (cepInput) {
    cepInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
        value = value.substring(0, 8); // Trava estritamente em 8 dígitos numéricos
        
        // Aplica a formatação
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        
        e.target.value = value;
    });
}

// ==========================================
// CONSULTA AUTOMÁTICA DE CEP (VIACEP API)
// ==========================================
const cepField = document.getElementById('cep');

if (cepField) {
    cepField.addEventListener('blur', function() {
        let cep = cepField.value.replace(/\D/g, ''); // Remove tudo que não for número

        // Verifica se o CEP possui exatamente 8 dígitos
        if (cep.length === 8) {
            // URL da API pública ViaCEP
            const url = `https://viacep.com.br/ws/${cep}/json/`;

            // Faz a requisição assíncrona (fetch)
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        // Preenche os inputs com os dados retornados pela API
                        document.getElementById('logradouro').value = data.logradouro || '';
                        document.getElementById('bairro').value = data.bairro || '';
                        document.getElementById('cidade').value = data.localidade || '';
                        document.getElementById('estado').value = data.uf || '';

                        // Remove eventuais erros visuais se os campos foram preenchidos
                        ['logradouro', 'bairro', 'cidade', 'estado'].forEach(id => {
                            document.getElementById(id).classList.remove('error');
                            const errElement = document.getElementById(`error-${id}`);
                            if (errElement) errElement.style.display = 'none';
                        });

                        // Joga o foco automaticamente para o campo "Número" para agilizar o preenchimento
                        document.getElementById('numero').focus();
                    } else {
                        alert('CEP não encontrado na base de dados.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar o CEP:', error);
                });
        }
    });
}