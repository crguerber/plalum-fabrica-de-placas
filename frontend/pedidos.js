let itensPedidoAtual = [];
let modoLeituraAtual = false;

async function listarPedidos(termo = '') {
    const lista = document.getElementById('lista-pedidos');
    if (!lista) return;

    lista.innerHTML = '<p>Carregando pedidos...</p>';
    
    try {
        const url = termo ? `${API_BASE_URL}/pedidos?termo=${encodeURIComponent(termo)}` : `${API_BASE_URL}/pedidos`;
        const resposta = await fetch(url);
        const textoBruto = await resposta.text();
        
        let dados;
        try {
            dados = JSON.parse(textoBruto);
        } catch (erroParse) {
            throw new Error("O servidor retornou um formato inválido.");
        }

        const arrayPedidos = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayPedidos && arrayPedidos.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID Pedido</th><th style="padding: 0.5rem;">Cliente</th><th style="padding: 0.5rem;">CPF</th><th style="padding: 0.5rem;">Data</th><th style="padding: 0.5rem;">Total</th><th style="padding: 0.5rem;">Situação</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayPedidos.forEach(pedido => {
                const pedidoJson = JSON.stringify(pedido).replace(/"/g, '&quot;');
                const valorFormatado = parseFloat(pedido.valorTotal || pedido.valor_total || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                const bloqueado = ['P', 'E', 'F'].includes(pedido.situacao);
                
                const botaoAcao = bloqueado 
                    ? `<button onclick="abrirModalPedido(${pedidoJson}, true)" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-secundaria); color: var(--cor-texto); border-radius: 4px;">Consultar</button>`
                    : `<button onclick="abrirModalPedido(${pedidoJson}, false)" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-fundo); color: var(--cor-texto); border-radius: 4px;">Editar</button>`;
                
                const botaoStatus = `<button onclick="abrirModalStatus('${pedido.idPedido}', '${pedido.situacao}')" style="padding: 0.25rem 0.5rem; cursor: pointer; border: 1px solid var(--cor-borda); background: var(--cor-primaria); color: white; border-radius: 4px;">Status</button>`;
                
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${pedido.idPedido || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${pedido.nomeCliente || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${pedido.cpfCliente || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${pedido.dataPedido || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${valorFormatado}</td>`;
                html += `<td style="padding: 0.5rem;">${pedido.situacao || '-'}</td>`;
                html += `<td style="padding: 0.5rem; display: flex; gap: 0.5rem;">${botaoAcao} ${botaoStatus}</td>`;
                html += `</tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<p>Nenhum pedido encontrado.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">${erro.message}</p>`;
    }
}

function filtrarPedidos() {
    listarPedidos(document.getElementById('termoBuscaPedido').value.trim());
}

function limparFiltroPedido() {
    document.getElementById('termoBuscaPedido').value = '';
    listarPedidos();
}

function abrirModalPedido(pedido = null, somenteLeitura = false) {
    document.getElementById('formPedido').reset();
    itensPedidoAtual = [];
    modoLeituraAtual = somenteLeitura;
    const titulo = document.getElementById('tituloModalPedido');
    const modal = document.getElementById('modalPedido');
    const displayCliente = document.getElementById('nomeClienteDisplay');
    
    if (pedido) {
        titulo.textContent = somenteLeitura ? 'Consultar Pedido' : 'Editar Pedido';
        document.getElementById('idPedido').value = pedido.idPedido;
        document.getElementById('idCliente').value = pedido.idCliente;
        
        displayCliente.textContent = `${pedido.nomeCliente || ''} - CPF: ${pedido.cpfCliente || ''}`;
        
        document.getElementById('dataPedido').value = pedido.dataPedido;
        
        let dataEntregaFormatada = pedido.data_entrega_prevista || pedido.dataEntregaPrevista;
        if (dataEntregaFormatada && dataEntregaFormatada.includes('-')) {
            const partes = dataEntregaFormatada.split('-');
            dataEntregaFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
        document.getElementById('dataEntrega').value = dataEntregaFormatada || '';
        
        const valorTotalCalculado = pedido.valor_total || pedido.valorTotal || 0;
        document.getElementById('valorTotal').value = parseFloat(valorTotalCalculado).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        const valorSinalCalculado = pedido.valor_sinal || pedido.valorSinal || 0;
        document.getElementById('valorSinal').value = parseFloat(valorSinalCalculado).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        document.getElementById('situacaoPedido').value = pedido.situacao;
        
        if (pedido.itens && Array.isArray(pedido.itens)) {
            itensPedidoAtual = pedido.itens;
        }
    } else {
        titulo.textContent = 'Novo Pedido';
        document.getElementById('idPedido').value = '';
        document.getElementById('dataPedido').value = new Date().toISOString().split('T')[0];
        document.getElementById('dataEntrega').value = '';
        document.getElementById('valorTotal').value = '';
        document.getElementById('valorSinal').value = '';
        document.getElementById('situacaoPedido').value = '';
        displayCliente.textContent = '';
    }
    
    renderizarTabelaItens();
    
    const inputsEditaveis = document.querySelectorAll('#idCliente, #dataPedido, #itemTabela, #itemCorPlaca, #itemCorLetra, #itemAltura, #itemLargura, #itemFrase');
    inputsEditaveis.forEach(input => input.disabled = somenteLeitura);
    
    const botoesAcao = document.querySelectorAll('#formPedido button:not([onclick="fecharModalPedido()"])');
    botoesAcao.forEach(btn => btn.style.display = somenteLeitura ? 'none' : 'block');
    
    modal.style.display = 'flex';
}

function fecharModalPedido() {
    document.getElementById('modalPedido').style.display = 'none';
}

function adicionarItem() {
    const inputTabela = document.getElementById('itemTabela');
    const inputCorPlaca = document.getElementById('itemCorPlaca');
    const inputCorLetra = document.getElementById('itemCorLetra');
    
    const idTabelaPrecos = inputTabela.value;
    const idCorPlaca = inputCorPlaca.value;
    const idCorLetra = inputCorLetra.value;
    const altura = parseFloat(document.getElementById('itemAltura').value) || 0;
    const largura = parseFloat(document.getElementById('itemLargura').value) || 0;
    const frase = document.getElementById('itemFrase').value || '';

    if (!idTabelaPrecos || !idCorPlaca || !idCorLetra || !altura || !largura) {
        alert("Preencha a tabela de preços, as cores e as dimensões da placa.");
        return;
    }

    const precoMaterial = parseFloat(inputTabela.dataset.preco_material) || 0;
    const precoLetra = parseFloat(inputTabela.dataset.preco_letra) || 0;

    const area = altura * largura;
    const custoMaterial = area * precoMaterial;
    const fraseSemEspacos = frase.replace(/\s/g, '');
    const custoLetras = fraseSemEspacos.length * precoLetra;
    const valorCalculado = custoMaterial + custoLetras;

    itensPedidoAtual.push({
        idTabelaPrecos: idTabelaPrecos,
        idCorPlaca: idCorPlaca,
        nomeCorPlaca: inputCorPlaca.dataset.nome || '',
        idCorLetra: idCorLetra,
        nomeCorLetra: inputCorLetra.dataset.nome || '',
        altura: altura,
        largura: largura,
        frase: frase,
        valor_calculado: valorCalculado
    });

    renderizarTabelaItens();
    atualizarTotaisInterface();

    inputTabela.value = '';
    inputCorPlaca.value = '';
    inputCorPlaca.dataset.nome = '';
    inputCorLetra.value = '';
    inputCorLetra.dataset.nome = '';
    document.getElementById('itemAltura').value = '';
    document.getElementById('itemLargura').value = '';
    document.getElementById('itemFrase').value = '';
}

function removerItem(index) {
    itensPedidoAtual.splice(index, 1);
    renderizarTabelaItens();
    atualizarTotaisInterface();
}

function atualizarTotaisInterface() {
    let total = 0;
    itensPedidoAtual.forEach(item => {
        total += parseFloat(item.valor_calculado || 0);
    });
    const sinal = total * 0.50;
    
    document.getElementById('valorTotal').value = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    document.getElementById('valorSinal').value = sinal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function renderizarTabelaItens() {
    const tbody = document.getElementById('corpoTabelaItens');
    tbody.innerHTML = '';
    
    itensPedidoAtual.forEach((item, index) => {
        const corPlacaExibicao = item.nomeCorPlaca || item.idCorPlaca || '-';
        const corLetraExibicao = item.nomeCorLetra || item.idCorLetra || '-';
        const valorFormatado = parseFloat(item.valor_calculado || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        const botaoRemover = modoLeituraAtual ? '' : `<button type="button" onclick="removerItem(${index})" style="color: red; border: none; background: none; cursor: pointer; font-weight: bold;">X</button>`;
        
        tbody.innerHTML += `
            <tr style="border-bottom: 1px solid var(--cor-borda);">
                <td style="padding: 0.5rem;">${item.idTabelaPrecos || '-'}</td>
                <td style="padding: 0.5rem;">${corPlacaExibicao}</td>
                <td style="padding: 0.5rem;">${corLetraExibicao}</td>
                <td style="padding: 0.5rem;">${item.altura || '-'}</td>
                <td style="padding: 0.5rem;">${item.largura || '-'}</td>
                <td style="padding: 0.5rem;">${item.frase || '-'}</td>
                <td style="padding: 0.5rem;">${valorFormatado}</td>
                <td style="padding: 0.5rem;">${botaoRemover}</td>
            </tr>
        `;
    });
}

async function guardarPedido(evento) {
    evento.preventDefault();
    const idPedido = document.getElementById('idPedido').value;
    const metodo = idPedido ? 'PUT' : 'POST';
    
    const dados = {
        idCliente: document.getElementById('idCliente').value,
        dataPedido: document.getElementById('dataPedido').value,
        data_entrega_prevista: '',
        valor_total: 0,
        valor_sinal: 0,
        situacao: 'A',
        itens: itensPedidoAtual
    };
    
    if (idPedido) {
        dados.idPedido = idPedido;
    }
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/pedidos`, {
            method: metodo,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const textoBruto = await resposta.text();
        
        try {
            const resultado = JSON.parse(textoBruto);
            if (resultado.sucesso) {
                alert('Pedido registado com sucesso!');
                
                const imprimir = document.getElementById('reciboImprimir').checked;
                const zap = document.getElementById('reciboZap').checked;
                const email = document.getElementById('reciboEmail').checked;
                
                if (imprimir || zap || email) {
                    const dadosRecibo = {
                        idPedido: resultado.idPedido,
                        dataPedido: document.getElementById('dataPedido').value,
                        dataEntrega: resultado.dataEntrega,
                        nomeCliente: document.getElementById('nomeClienteDisplay').textContent,
                        telefone: document.getElementById('idCliente').dataset.telefone || '',
                        email: document.getElementById('idCliente').dataset.email || '',
                        valorTotal: resultado.valorTotal,
                        valorSinal: resultado.valorSinal,
                        itens: itensPedidoAtual
                    };
                    processarRecibos(dadosRecibo, imprimir, zap, email);
                }
                
                fecharModalPedido();
                listarPedidos();
            } else {
                alert('Erro: ' + resultado.mensagem);
            }
        } catch (erroParse) {
            console.error("Erro oculto retornado pelo servidor:", textoBruto);
            alert('O servidor retornou um erro interno ao gravar o pedido. Verifique o console.');
        }
    } catch (erro) {
        console.error("Falha na requisição Fetch:", erro);
        alert('Ocorreu um erro de rede ao comunicar com o servidor.');
    }
}

let campoDestinoAtual = '';
let dadosModalAtual = [];
let colunasModalAtual = [];

async function abrirModalBusca(entidade, campoDestino, colunas) {
    campoDestinoAtual = campoDestino;
    colunasModalAtual = colunas;
    const modal = document.getElementById('modalBuscaGenerica');
    const conteudo = document.getElementById('conteudoBuscaGenerica');
    document.getElementById('termoBuscaGenerica').value = '';
    
    modal.style.display = 'flex';
    conteudo.innerHTML = '<p>Carregando dados...</p>';

    try {
        const resposta = await fetch(`${API_BASE_URL}/${entidade}`);
        const textoBruto = await resposta.text();
        const dados = JSON.parse(textoBruto);
        dadosModalAtual = Array.isArray(dados) ? dados : (dados.dados || []);
        
        renderizarTabelaBusca(dadosModalAtual);
    } catch (erro) {
        conteudo.innerHTML = `<p style="color: red;">Erro ao processar dados: ${erro.message}</p>`;
    }
}

function renderizarTabelaBusca(arrayRegistos) {
    const conteudo = document.getElementById('conteudoBuscaGenerica');
    if (arrayRegistos.length > 0) {
        let html = '<table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">';
        html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
        
        colunasModalAtual.forEach(coluna => {
            html += `<th style="padding: 0.5rem; text-transform: capitalize;">${coluna.replace('_', ' ')}</th>`;
        });
        html += '<th style="padding: 0.5rem;">Ação</th></tr>';

        arrayRegistos.forEach(registo => {
            html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
            colunasModalAtual.forEach(coluna => {
                html += `<td style="padding: 0.5rem;">${registo[coluna] || '-'}</td>`;
            });
            html += `<td style="padding: 0.5rem;">
                        <button type="button" onclick="selecionarRegisto('${registo[colunasModalAtual[0]]}')" style="padding: 0.25rem 0.5rem; background-color: var(--cor-primaria); color: white; border: none; border-radius: 4px; cursor: pointer;">Selecionar</button>
                     </td></tr>`;
        });
        html += '</table>';
        conteudo.innerHTML = html;
    } else {
        conteudo.innerHTML = '<p>Nenhum registo encontrado.</p>';
    }
}

function filtrarBuscaGenerica() {
    const termo = document.getElementById('termoBuscaGenerica').value.toLowerCase();
    const dadosFiltrados = dadosModalAtual.filter(registo => {
        return colunasModalAtual.some(coluna => {
            const valor = registo[coluna];
            return valor && valor.toString().toLowerCase().includes(termo);
        });
    });
    renderizarTabelaBusca(dadosFiltrados);
}

function selecionarRegisto(idSelecionado) {
    if (campoDestinoAtual) {
        const inputDestino = document.getElementById(campoDestinoAtual);
        inputDestino.value = idSelecionado;

        const registo = dadosModalAtual.find(r => r[colunasModalAtual[0]] == idSelecionado);
        if (registo) {
            if (registo.nome) inputDestino.dataset.nome = registo.nome;
            if (registo.preco_material) inputDestino.dataset.preco_material = registo.preco_material;
            if (registo.preco_letra) inputDestino.dataset.preco_letra = registo.preco_letra;
            if (registo.cpf) inputDestino.dataset.cpf = registo.cpf;
            if (registo.telefone) inputDestino.dataset.telefone = registo.telefone;
            if (registo.email) inputDestino.dataset.email = registo.email;

            if (campoDestinoAtual === 'idCliente') {
                const nomeExibicao = registo.nome || '';
                const cpfExibicao = registo.cpf || '';
                document.getElementById('nomeClienteDisplay').textContent = `${nomeExibicao} - CPF: ${cpfExibicao}`;
            }
        } else {
            inputDestino.dataset.nome = '';
            inputDestino.dataset.preco_material = '';
            inputDestino.dataset.preco_letra = '';
            inputDestino.dataset.cpf = '';
            inputDestino.dataset.telefone = '';
            inputDestino.dataset.email = '';
            if (campoDestinoAtual === 'idCliente') {
                document.getElementById('nomeClienteDisplay').textContent = '';
            }
        }
    }
    fecharModalBusca();
}

function fecharModalBusca() {
    document.getElementById('modalBuscaGenerica').style.display = 'none';
    campoDestinoAtual = '';
    dadosModalAtual = [];
    colunasModalAtual = [];
}

function abrirModalStatus(id, situacaoAtual) {
    document.getElementById('idPedidoStatus').value = id;
    const select = document.getElementById('novoStatus');
    select.innerHTML = '';
    
    let opcoes = [];
    if (situacaoAtual === 'A') {
        opcoes = [{val: 'P', text: 'Em Produção'}, {val: 'F', text: 'Finalizado'}, {val: 'C', text: 'Cancelado'}];
    } else if (situacaoAtual === 'P') {
        opcoes = [{val: 'F', text: 'Finalizado'}, {val: 'E', text: 'Entregue'}];
    } else if (situacaoAtual === 'F') {
        opcoes = [{val: 'E', text: 'Entregue'}];
    }
    
    if (opcoes.length === 0) {
        alert('Este pedido já atingiu um estado que não permite novas alterações de situação.');
        return;
    }
    
    opcoes.forEach(op => {
        select.innerHTML += `<option value="${op.val}">${op.text}</option>`;
    });
    
    document.getElementById('modalStatusPedido').style.display = 'flex';
}

function fecharModalStatus() {
    document.getElementById('modalStatusPedido').style.display = 'none';
}

async function salvarNovoStatus() {
    const id = document.getElementById('idPedidoStatus').value;
    const status = document.getElementById('novoStatus').value;
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/pedidos`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idPedido: id, situacao: status })
        });
        
        const textoBruto = await resposta.text();
        const resultado = JSON.parse(textoBruto);
        
        if (resultado.sucesso) {
            alert('Situação atualizada com sucesso!');
            fecharModalStatus();
            listarPedidos();
        } else {
            alert('Erro: ' + resultado.mensagem);
        }
    } catch (erro) {
        alert('Ocorreu um erro de rede ao tentar atualizar o status.');
    }
}

function processarRecibos(dados, imprimir, zap, email) {
    let textoItensSimples = '';
    let htmlItens = '';
    
    dados.itens.forEach(i => {
        const desc = `Placa (${i.altura}x${i.largura}m) - Frase: "${i.frase}"`;
        const val = parseFloat(i.valor_calculado || 0).toFixed(2);
        textoItensSimples += `- 1x ${desc} - R$ ${val}\n`;
        htmlItens += `<div>1x ${desc} <span style="float:right;">R$ ${val}</span></div>`;
    });

    const saldo = (parseFloat(dados.valorTotal) - parseFloat(dados.valorSinal)).toFixed(2);
    const totalFormat = parseFloat(dados.valorTotal).toFixed(2);
    const sinalFormat = parseFloat(dados.valorSinal).toFixed(2);

    const textoMensagem = 
        `*RECIBO DE ENCOMENDA*\n` +
        `Associação de Apoio\n` +
        `---------------------------\n` +
        `Pedido Nº: ${dados.idPedido}\n` +
        `Data: ${dados.dataPedido}\n` +
        `Previsão de Entrega: ${dados.dataEntrega}\n` +
        `Cliente: ${dados.nomeCliente}\n` +
        `---------------------------\n` +
        `*ITENS DO PEDIDO*\n${textoItensSimples}` +
        `---------------------------\n` +
        `Valor Total: R$ ${totalFormat}\n` +
        `Sinal Pago (50%): R$ ${sinalFormat}\n` +
        `*Saldo a Pagar: R$ ${saldo}*\n` +
        `---------------------------\n` +
        `Obrigado pela preferência!`;

    if (zap && dados.telefone) {
        const numZap = dados.telefone.replace(/\D/g, ''); // Limpa a formatação do número
        window.open(`https://wa.me/55${numZap}?text=${encodeURIComponent(textoMensagem)}`, '_blank');
    } else if (zap && !dados.telefone) {
        alert("Não foi possível enviar o WhatsApp pois o cliente não possui telefone registado.");
    }

    if (email && dados.email) {
        window.open(`mailto:${dados.email}?subject=Recibo de Encomenda Nº ${dados.idPedido}&body=${encodeURIComponent(textoMensagem)}`, '_blank');
    } else if (email && !dados.email) {
        alert("Não foi possível enviar o e-mail pois o cliente não possui endereço registado.");
    }

    if (imprimir) {
        const divImpressao = document.getElementById('areaImpressaoRecibo');
        const htmlVia = `
            <div class="via-recibo">
                <h2 style="text-align:center; margin: 0 0 10px 0;">RECIBO DE ENCOMENDA</h2>
                <p><strong>Pedido Nº:</strong> ${dados.idPedido} <span style="float:right;"><strong>Data:</strong> ${dados.dataPedido}</span></p>
                <p><strong>Cliente:</strong> ${dados.nomeCliente}</p>
                <p><strong>Previsão de Entrega:</strong> ${dados.dataEntrega}</p>
                <hr style="border-top: 1px dashed #000;">
                <div style="margin: 15px 0;">${htmlItens}</div>
                <hr style="border-top: 1px dashed #000;">
                <p><strong>Valor Total:</strong> <span style="float:right;">R$ ${totalFormat}</span></p>
                <p><strong>Sinal Pago (50%):</strong> <span style="float:right;">R$ ${sinalFormat}</span></p>
                <p><strong>Saldo a Pagar:</strong> <span style="float:right;"><strong>R$ ${saldo}</strong></span></p>
                <br><br><br>
                <p style="text-align:center;">____________________________________________________<br>Assinatura do Responsável</p>
            </div>
        `;
        
        divImpressao.innerHTML = 
            `<h3 style="text-align:center; font-family: monospace;">VIA DA ASSOCIAÇÃO</h3>` + htmlVia + 
            `<div style="page-break-before: always; height: 20px;"></div>` +
            `<h3 style="text-align:center; font-family: monospace;">VIA DO CLIENTE</h3>` + htmlVia;
        
        setTimeout(() => { window.print(); }, 500);
    }
}

function dispararReciboManual() {
    const idPedido = document.getElementById('idPedido').value;
    if (!idPedido) {
        alert('É necessário guardar o pedido primeiro para que o sistema gere o número do recibo.');
        return;
    }

    const imprimir = document.getElementById('reciboImprimir').checked;
    const zap = document.getElementById('reciboZap').checked;
    const email = document.getElementById('reciboEmail').checked;

    if (!imprimir && !zap && !email) {
        alert('Selecione ao menos uma opção de emissão.');
        return;
    }

    let total = 0;
    itensPedidoAtual.forEach(item => {
        total += parseFloat(item.valor_calculado || 0);
    });
    const sinal = total * 0.50;

    const dadosRecibo = {
        idPedido: idPedido,
        dataPedido: document.getElementById('dataPedido').value,
        dataEntrega: document.getElementById('dataEntrega').value || 'A calcular',
        nomeCliente: document.getElementById('nomeClienteDisplay').textContent,
        telefone: document.getElementById('idCliente').dataset.telefone || '',
        email: document.getElementById('idCliente').dataset.email || '',
        valorTotal: total,
        valorSinal: sinal,
        itens: itensPedidoAtual
    };

    processarRecibos(dadosRecibo, imprimir, zap, email);
}