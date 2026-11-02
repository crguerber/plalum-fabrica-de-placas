async function listarClientes(termo = '') {
    const lista = document.getElementById('lista-clientes');
    if (!lista) return;

    lista.innerHTML = '<p>Carregando clientes...</p>';
    
    try {
        const url = termo ? `${API_BASE_URL}/clientes?nome=${encodeURIComponent(termo)}&cpf=${encodeURIComponent(termo)}` : `${API_BASE_URL}/clientes`;
        const resposta = await fetch(url);
        const textoBruto = await resposta.text();
        
        let dados;
        try { dados = JSON.parse(textoBruto); } catch (e) { throw new Error("Formato inválido."); }

        const arrayClientes = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayClientes && arrayClientes.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID</th><th style="padding: 0.5rem;">Nome</th><th style="padding: 0.5rem;">CPF</th><th style="padding: 0.5rem;">Cidade/UF</th><th style="padding: 0.5rem;">Status</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayClientes.forEach(cliente => {
                const textoStatus = cliente.ativo == 1 ? 'Ativo' : 'Inativo';
                const novoStatus = cliente.ativo == 1 ? 0 : 1;
                const corBtn = cliente.ativo == 1 ? '#dc3545' : '#28a745';
                const txtBtn = cliente.ativo == 1 ? 'Inativar' : 'Ativar';
                const cidadeUf = cliente.nome_cidade ? `${cliente.nome_cidade}/${cliente.uf_sigla}` : '-';
                
                const clienteJson = JSON.stringify(cliente).replace(/"/g, '&quot;');
                
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${cliente.idCliente || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.nome || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.cpf || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cidadeUf}</td>`;
                html += `<td style="padding: 0.5rem;">${textoStatus}</td>`;
                html += `<td style="padding: 0.5rem; display: flex; gap: 0.5rem;">
                            <button onclick="abrirModalCliente(${clienteJson})" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-fundo); border-radius: 4px;">Editar</button>
                            <button onclick="alterarStatusCliente(${cliente.idCliente}, ${novoStatus})" style="padding: 0.25rem 0.5rem; cursor: pointer; background-color: ${corBtn}; color: white; border: none; border-radius: 4px;">${txtBtn}</button>
                         </td></tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<p>Nenhum cliente encontrado.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">${erro.message}</p>`;
    }
}

async function carregarUfs(ufSelecionada = null) {
    const selectUf = document.getElementById('ufCliente');
    try {
        const resposta = await fetch(`${API_BASE_URL}/ufs`);
        const resultado = await resposta.json();
        
        selectUf.innerHTML = '<option value="">Selecione o Estado</option>';
        if (resultado.sucesso) {
            resultado.dados.forEach(uf => {
                const option = document.createElement('option');
                option.value = uf.uf_id;
                option.textContent = uf.nome;
                if (uf.uf_id == ufSelecionada) option.selected = true;
                selectUf.appendChild(option);
            });
        }
    } catch (erro) {
        console.error("Erro ao carregar UFs", erro);
    }
}

async function carregarCidades(uf_id, cidadeSelecionada = null) {
    const selectCidade = document.getElementById('cidadeCliente');
    selectCidade.innerHTML = '<option value="">Selecione a Cidade</option>';
    
    if (!uf_id) return;

    try {
        const resposta = await fetch(`${API_BASE_URL}/cidades?uf_id=${uf_id}`);
        const resultado = await resposta.json();
        
        if (resultado.sucesso) {
            resultado.dados.forEach(cidade => {
                const option = document.createElement('option');
                option.value = cidade.id_cidade;
                option.textContent = cidade.nome;
                if (cidade.id_cidade == cidadeSelecionada) option.selected = true;
                selectCidade.appendChild(option);
            });
        }
    } catch (erro) {
        console.error("Erro ao carregar Cidades", erro);
    }
}

function filtrarClientes() {
    listarClientes(document.getElementById('termoBusca').value.trim());
}

function limparFiltro() {
    document.getElementById('termoBusca').value = '';
    listarClientes();
}

async function abrirModalCliente(cliente = null) {
    document.getElementById('formCliente').reset();
    document.getElementById('cidadeCliente').innerHTML = '<option value="">Selecione a Cidade</option>';
    
    if (cliente) {
        document.getElementById('tituloModalCliente').textContent = 'Editar Cliente';
        document.getElementById('idCliente').value = cliente.idCliente;
        document.getElementById('nomeCliente').value = cliente.nome || '';
        document.getElementById('cpfCliente').value = cliente.cpf || '';
        document.getElementById('telefoneCliente').value = cliente.telefone || '';
        document.getElementById('emailCliente').value = cliente.email || '';
        document.getElementById('tipoCliente').value = cliente.tipo_cliente || '';
        document.getElementById('cepCliente').value = cliente.cep || '';
        document.getElementById('bairroCliente').value = cliente.bairro || '';
        document.getElementById('logradouroCliente').value = cliente.logradouro || '';
        document.getElementById('numeroCliente').value = cliente.numero || '';
        document.getElementById('complementoCliente').value = cliente.complemento || '';
        document.getElementById('observacaoCliente').value = cliente.observacao || '';
        
        await carregarUfs(cliente.uf_id);
        if (cliente.uf_id) {
            await carregarCidades(cliente.uf_id, cliente.cidade_id);
        }
    } else {
        document.getElementById('tituloModalCliente').textContent = 'Novo Cliente';
        document.getElementById('idCliente').value = '';
        await carregarUfs();
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
        email: document.getElementById('emailCliente').value,
        tipo_cliente: document.getElementById('tipoCliente').value,
        cep: document.getElementById('cepCliente').value,
        cidade_id: document.getElementById('cidadeCliente').value || null,
        bairro: document.getElementById('bairroCliente').value,
        logradouro: document.getElementById('logradouroCliente').value,
        numero: document.getElementById('numeroCliente').value,
        complemento: document.getElementById('complementoCliente').value,
        observacao: document.getElementById('observacaoCliente').value
    };
    
    if (idCliente) dados.idCliente = idCliente;
    
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

async function alterarStatusCliente(idCliente, novoStatus) {
    if (!confirm(`Tem a certeza que deseja ${novoStatus === 1 ? 'ativar' : 'inativar'} este cliente?`)) return;

    try {
        const resposta = await fetch(`${API_BASE_URL}/clientes`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idCliente: idCliente, ativo: novoStatus })
        });
        
        const resultado = await resposta.json();
        if (resultado.sucesso) {
            listarClientes();
        } else {
            alert('Erro ao atualizar situação: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Erro ao comunicar com a API.');
    }
}