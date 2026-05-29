async function listarCores(termo = '') {
    const lista = document.getElementById('lista-cores');
    if (!lista) return;

    lista.innerHTML = '<p>A carregar cores...</p>';
    
    try {
        const url = termo ? `${API_BASE_URL}/cores?nome=${encodeURIComponent(termo)}` : `${API_BASE_URL}/cores`;
        const resposta = await fetch(url);
        const textoBruto = await resposta.text();
        
        let dados;
        try {
            dados = JSON.parse(textoBruto);
        } catch (erroParse) {
            throw new Error("O servidor retornou um formato inválido.");
        }

        const arrayCores = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayCores && arrayCores.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID</th><th style="padding: 0.5rem;">Nome</th><th style="padding: 0.5rem;">Tipo</th><th style="padding: 0.5rem;">Status</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayCores.forEach(cor => {
                const textoStatus = cor.ativo == 1 ? 'Ativo' : 'Inativo';
                const novoStatus = cor.ativo == 1 ? 0 : 1;
                const corBotaoStatus = cor.ativo == 1 ? '#dc3545' : '#28a745';
                const textoBotaoStatus = cor.ativo == 1 ? 'Inativar' : 'Ativar';
                const corJson = JSON.stringify(cor).replace(/"/g, '&quot;');
                
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${cor.idCor || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cor.nome || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cor.tipo || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${textoStatus}</td>`;
                html += `<td style="padding: 0.5rem; display: flex; gap: 0.5rem;">
                            <button onclick="abrirModalCor(${corJson})" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-fundo); color: var(--cor-texto); border-radius: 4px;">Editar</button>
                            <button onclick="alterarStatusCor(${cor.idCor}, ${novoStatus})" style="padding: 0.25rem 0.5rem; cursor: pointer; background-color: ${corBotaoStatus}; color: white; border: none; border-radius: 4px;">${textoBotaoStatus}</button>
                         </td>`;
                html += `</tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<p>Nenhuma cor encontrada para esta busca.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">${erro.message}</p>`;
    }
}

function filtrarCores() {
    const termo = document.getElementById('termoBuscaCor').value.trim();
    listarCores(termo);
}

function limparFiltroCor() {
    document.getElementById('termoBuscaCor').value = '';
    listarCores();
}

function abrirModalCor(cor = null) {
    document.getElementById('formCor').reset();
    const titulo = document.getElementById('tituloModalCor');
    const modal = document.getElementById('modalCor');
    
    if (cor) {
        titulo.textContent = 'Editar Cor';
        document.getElementById('idCor').value = cor.idCor;
        document.getElementById('nomeCor').value = cor.nome;
        document.getElementById('tipoCor').value = cor.tipo || '';
    } else {
        titulo.textContent = 'Nova Cor';
        document.getElementById('idCor').value = '';
    }
    
    modal.style.display = 'flex';
}

function fecharModalCor() {
    document.getElementById('modalCor').style.display = 'none';
}

async function guardarCor(evento) {
    evento.preventDefault();
    const idCor = document.getElementById('idCor').value;
    const metodo = idCor ? 'PUT' : 'POST';
    
    const dados = {
        nome: document.getElementById('nomeCor').value,
        tipo: document.getElementById('tipoCor').value
    };
    
    if (idCor) {
        dados.idCor = idCor;
    }
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/cores`, {
            method: metodo,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const resultado = await resposta.json();
        if (resultado.sucesso) {
            alert('Operação realizada com sucesso!');
            fecharModalCor();
            listarCores();
        } else {
            alert('Erro: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Ocorreu um erro ao comunicar com o servidor.');
    }
}

async function alterarStatusCor(idCor, novoStatus) {
    const acaoTexto = novoStatus === 1 ? 'ativar' : 'inativar';
    if (!confirm(`Tem a certeza que deseja ${acaoTexto} esta cor?`)) return;

    try {
        const resposta = await fetch(`${API_BASE_URL}/cores/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idCor: idCor, ativo: novoStatus })
        });
        
        const resultado = await resposta.json();
        
        if (resultado.sucesso) {
            alert('Situação atualizada com sucesso!');
            listarCores();
        } else {
            alert('Erro ao atualizar situação: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Erro ao comunicar com a API.');
    }
}