async function listarClientes(termo = '') {
    const lista = document.getElementById('lista-clientes');
    if (!lista) return;

    lista.innerHTML = '<p>Carregando clientes...</p>';
    
    try {
        const url = termo ? `${API_BASE_URL}/clientes?nome=${encodeURIComponent(termo)}&cpf=${encodeURIComponent(termo)}` : `${API_BASE_URL}/clientes`;
        const resposta = await fetch(url);
        const textoBruto = await resposta.text();
        
        let dados;
        try {
            dados = JSON.parse(textoBruto);
        } catch (erroParse) {
            console.error("Falha ao interpretar JSON:", textoBruto);
            throw new Error("O servidor retornou um formato inválido.");
        }

        const arrayClientes = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayClientes && arrayClientes.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID</th><th style="padding: 0.5rem;">Nome</th><th style="padding: 0.5rem;">CPF</th><th style="padding: 0.5rem;">Status</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayClientes.forEach(cliente => {
                const textoStatus = cliente.ativo == 1 ? 'Ativo' : 'Inativo';
                const novoStatus = cliente.ativo == 1 ? 0 : 1;
                const corBotaoStatus = cliente.ativo == 1 ? '#dc3545' : '#28a745';
                const textoBotaoStatus = cliente.ativo == 1 ? 'Inativar' : 'Ativar';
                const clienteJson = JSON.stringify(cliente).replace(/"/g, '&quot;');
                
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${cliente.idCliente || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.nome || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.cpf || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${textoStatus}</td>`;
                html += `<td style="padding: 0.5rem; display: flex; gap: 0.5rem;">
                            <button onclick="abrirModalCliente(${clienteJson})" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-fundo); color: var(--cor-texto); border-radius: 4px;">Editar</button>
                            <button onclick="alterarStatusCliente(${cliente.idCliente}, ${novoStatus})" style="padding: 0.25rem 0.5rem; cursor: pointer; background-color: ${corBotaoStatus}; color: white; border: none; border-radius: 4px;">${textoBotaoStatus}</button>
                         </td>`;
                html += `</tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<p>Nenhum cliente encontrado para esta busca.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">${erro.message}</p>`;
    }
}

function filtrarClientes() {
    const termo = document.getElementById('termoBusca').value.trim();
    listarClientes(termo);
}

function limparFiltro() {
    document.getElementById('termoBusca').value = '';
    listarClientes();
}

function abrirModalCliente(cliente = null) {
    document.getElementById('formCliente').reset();
    const titulo = document.getElementById('tituloModalCliente');
    
    if (cliente) {
        titulo.textContent = 'Editar Cliente';
        document.getElementById('idCliente').value = cliente.idCliente;
        document.getElementById('nomeCliente').value = cliente.nome;
        document.getElementById('cpfCliente').value = cliente.cpf;
        document.getElementById('telefoneCliente').value = cliente.telefone || '';
        document.getElementById('emailCliente').value = cliente.email || '';
    } else {
        titulo.textContent = 'Novo Cliente';
        document.getElementById('idCliente').value = '';
    }
    
    document.getElementById('modalCliente').classList.add('ativo');
}

function fecharModalCliente() {
    document.getElementById('modalCliente').classList.remove('ativo');
}

async function guardarCliente(evento) {
    evento.preventDefault();
    const idCliente = document.getElementById('idCliente').value;
    const metodo = idCliente ? 'PUT' : 'POST';
    
    const dados = {
        nome: document.getElementById('nomeCliente').value,
        cpf: document.getElementById('cpfCliente').value,
        telefone: document.getElementById('telefoneCliente').value,
        email: document.getElementById('emailCliente').value
    };
    
    if (idCliente) {
        dados.idCliente = idCliente;
        // A linha "dados.ativo = ..." que estava aqui foi excluída com sucesso!
    }
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/clientes`, {
            method: metodo,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const resultado = await resposta.json();
        if (resultado.sucesso) {
            alert('Operação realizada com sucesso!');
            fecharModalCliente();
            listarClientes();
        } else {
            alert('Erro: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Ocorreu um erro ao comunicar com o servidor.');
    }
}

// NOVA FUNÇÃO: Exclusiva para a alteração de situação (Ativar/Inativar)
async function alterarStatusCliente(idCliente, novoStatus) {
    const acaoTexto = novoStatus === 1 ? 'ativar' : 'inativar';
    if (!confirm(`Tem a certeza que deseja ${acaoTexto} este cliente?`)) return;

    try {
        // ATENÇÃO: Ajuste a URL abaixo para a rota exata que você criou no servidor para mudar o status
        const resposta = await fetch(`${API_BASE_URL}/clientes/alterarStatus`, {
            method: 'PUT', // ou PATCH, dependendo de como você programou no backend
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idCliente: idCliente, ativo: novoStatus })
        });
        
        const resultado = await resposta.json();
        
        if (resultado.sucesso) {
            alert('Situação atualizada com sucesso!');
            listarClientes(); // Atualiza a tabela na tela imediatamente
        } else {
            alert('Erro ao atualizar situação: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Erro ao comunicar com a API.');
        console.error(erro);
    }
}