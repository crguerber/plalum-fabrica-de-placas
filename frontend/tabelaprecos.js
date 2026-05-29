async function listarPrecos(termo = '') {
    const lista = document.getElementById('lista-precos');
    if (!lista) return;

    lista.innerHTML = '<p>A carregar tabela de preços...</p>';
    
    try {
        const url = termo ? `${API_BASE_URL}/tabela-precos?dataVigencia=${encodeURIComponent(termo)}` : `${API_BASE_URL}/tabela-precos`;
        const resposta = await fetch(url);
        const textoBruto = await resposta.text();
        
        let dados;
        try {
            dados = JSON.parse(textoBruto);
        } catch (erroParse) {
            throw new Error("O servidor retornou um formato inválido.");
        }

        const arrayPrecos = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayPrecos && arrayPrecos.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID</th><th style="padding: 0.5rem;">Vigência</th><th style="padding: 0.5rem;">R$ Material</th><th style="padding: 0.5rem;">R$ Letra</th><th style="padding: 0.5rem;">Status</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayPrecos.forEach(preco => {
                const textoStatus = preco.ativo == 1 ? 'Ativo' : 'Inativo';
                const novoStatus = preco.ativo == 1 ? 0 : 1;
                const corBotaoStatus = preco.ativo == 1 ? '#dc3545' : '#28a745';
                const textoBotaoStatus = preco.ativo == 1 ? 'Inativar' : 'Ativar';
                const precoJson = JSON.stringify(preco).replace(/"/g, '&quot;');
                
                let dataFormatada = '-';
                if (preco.dataVigencia) {
                    const partesData = preco.dataVigencia.split('-');
                    dataFormatada = `${partesData[2]}/${partesData[1]}/${partesData[0]}`;
                }

                const valorMaterial = parseFloat(preco.preco_material || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                const valorLetra = parseFloat(preco.preco_letra || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${preco.idTabelaPrecos || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${dataFormatada}</td>`;
                html += `<td style="padding: 0.5rem;">${valorMaterial}</td>`;
                html += `<td style="padding: 0.5rem;">${valorLetra}</td>`;
                html += `<td style="padding: 0.5rem;">${textoStatus}</td>`;
                html += `<td style="padding: 0.5rem; display: flex; gap: 0.5rem;">
                            <button onclick="abrirModalPreco(${precoJson})" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-fundo); color: var(--cor-texto); border-radius: 4px;">Editar</button>
                            <button onclick="alterarStatusPreco(${preco.idTabelaPrecos}, ${novoStatus})" style="padding: 0.25rem 0.5rem; cursor: pointer; background-color: ${corBotaoStatus}; color: white; border: none; border-radius: 4px;">${textoBotaoStatus}</button>
                         </td>`;
                html += `</tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<p>Nenhum registro encontrado para esta busca.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">${erro.message}</p>`;
    }
}

function filtrarPrecos() {
    const termo = document.getElementById('termoBuscaPreco').value.trim();
    listarPrecos(termo);
}

function limparFiltroPreco() {
    document.getElementById('termoBuscaPreco').value = '';
    listarPrecos();
}

function abrirModalPreco(preco = null) {
    document.getElementById('formPreco').reset();
    const titulo = document.getElementById('tituloModalPreco');
    const modal = document.getElementById('modalPreco');
    
    if (preco) {
        titulo.textContent = 'Editar Tabela de Preços';
        document.getElementById('idTabelaPrecos').value = preco.idTabelaPrecos;
        document.getElementById('dataVigencia').value = preco.dataVigencia;
        document.getElementById('precoMaterial').value = preco.preco_material;
        document.getElementById('precoLetra').value = preco.preco_letra;
    } else {
        titulo.textContent = 'Nova Tabela de Preços';
        document.getElementById('idTabelaPrecos').value = '';
    }
    
    modal.style.display = 'flex';
}

function fecharModalPreco() {
    document.getElementById('modalPreco').style.display = 'none';
}

async function guardarPreco(evento) {
    evento.preventDefault();
    const idTabelaPrecos = document.getElementById('idTabelaPrecos').value;
    const metodo = idTabelaPrecos ? 'PUT' : 'POST';
    
    const dados = {
        dataVigencia: document.getElementById('dataVigencia').value,
        preco_material: parseFloat(document.getElementById('precoMaterial').value),
        preco_letra: parseFloat(document.getElementById('precoLetra').value)
    };
    
    if (idTabelaPrecos) {
        dados.idTabelaPrecos = idTabelaPrecos;
    }
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/tabela-precos`, {
            method: metodo,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const textoBruto = await resposta.text();
        
        try {
            const resultado = JSON.parse(textoBruto);
            if (resultado.sucesso) {
                alert('Operação realizada com sucesso!');
                fecharModalPreco();
                listarPrecos();
            } else {
                alert('Erro: ' + resultado.mensagem);
            }
        } catch (erroParse) {
            //console.error("Erro oculto retornado pelo PHP:", textoBruto);
            alert('O servidor retornou um erro interno ou HTML inválido. Verifique o console.');
        }
    } catch (erro) {
        console.error("Falha na requisição Fetch:", erro);
        alert('Ocorreu um erro de rede ao comunicar com o servidor.');
    }

}

async function alterarStatusPreco(idTabelaPrecos, novoStatus) {
    const acaoTexto = novoStatus === 1 ? 'ativar' : 'inativar';
    if (!confirm(`Tem a certeza que deseja ${acaoTexto} este registro?`)) return;

    try {
        const resposta = await fetch(`${API_BASE_URL}/tabela-precos/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idTabelaPrecos: idTabelaPrecos, ativo: novoStatus })
        });
        
        const resultado = await resposta.json();
        
        if (resultado.sucesso) {
            alert('Situação atualizada com sucesso!');
            listarPrecos();
        } else {
            alert('Erro ao atualizar situação: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Erro ao comunicar com a API.');
    }
}