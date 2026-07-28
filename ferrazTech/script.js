document.getElementById('budgetForm').addEventListener('submit', function(e) {
            let isValid = true;

            // Campos a validar
            const fields = [
                { id: 'nome', errorId: 'error-nome' },
                { id: 'telefone', errorId: 'error-telefone' },
                { id: 'email', errorId: 'error-email' },
                { id: 'cpf', errorId: 'error-cpf' },
                { id: 'tipo_residencia', errorId: 'error-tipo_residencia' },
                { id: 'endereco', errorId: 'error-endereco' },
                { id: 'tipo_solicitacao', errorId: 'error-tipo_solicitacao' }
            ];

            fields.forEach(field => {
                const input = document.getElementById(field.id);
                const error = document.getElementById(field.errorId);

                if (!input.value.trim()) {
                    input.classList.add('error');
                    error.style.display = 'block';
                    isValid = false;
                } else {
                    input.classList.remove('error');
                    error.style.display = 'none';
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
            }
        });