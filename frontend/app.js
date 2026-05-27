const API_BASE_URL = 'http://localhost:8000/api';

// --- CONTROLES DE INTERFACE ---
document.getElementById('btnMenu').addEventListener('click', function() {
    document.getElementById('navegacao').classList.toggle('ativa');
});

document.getElementById('checkboxTema').addEventListener('change', function() {
    if (this.checked) {
        document.body.setAttribute('data-theme', 'dark');
    } else {
        document.body.removeAttribute('data-theme');
    }
});

function mudarAba(idAba, elementoClicado) {
    document.querySelectorAll('.aba').forEach(btn => btn.classList.remove('ativa'));
    document.querySelectorAll('.conteudo-aba').forEach(conteudo => conteudo.classList.remove('ativo'));
    
    if(elementoClicado) elementoClicado.classList.add('ativa');
    const abaAlvo = document.getElementById(`aba-${idAba}`);
    if(abaAlvo) abaAlvo.classList.add('ativo');
}

// --- ROTEAMENTO E INTEGRAÇÃO ---
function carregarVista(vista) {
    console.log("Passo 1: carregarVista acionada. Módulo:", vista);
    
    document.getElementById('navegacao').classList.remove('ativa');
    const conteudoPrincipal = document.getElementById('conteudoPrincipal');
    
    if (vista === 'dashboard') {
        conteudoPrincipal.innerHTML = `<h2>Carregando Painel de Controle...</h2>`;
    } else {
        conteudoPrincipal.innerHTML = `
            <section class="vista-modulo">
                <h2>Módulo: ${vista.charAt(0).toUpperCase() + vista.slice(1)}</h2>
                <div class="acoes-modulo">
                    <button class="btn-nav" onclick="alert('Formulário de inserção em breve')">Novo Registro</button>
                </div>
                <div id="lista-${vista}" class="listagem-dados" style="margin-top: 1.5rem;">
                    <p>A inicializar comunicação com a API...</p>
                </div>
            </section>
        `;
        
        if (vista === 'clientes') {
            console.log("Passo 2: Condição de clientes confirmada. Invocando listarClientes().");
            listarClientes();
        }
    }
}

async function listarClientes() {
    console.log("Passo 3: listarClientes() iniciada. Preparando fetch...");
    const lista = document.getElementById('lista-clientes');
    
    if (!lista) {
        console.error("ERRO: O contêiner 'lista-clientes' não existe no DOM.");
        return;
    }

    lista.innerHTML = '<p>A carregar clientes...</p>';
    
    try {
        const resposta = await fetch(`${API_BASE_URL}/clientes`);
        console.log("Passo 4: Resposta do servidor recebida. Status HTTP:", resposta.status);
        
        const dados = await resposta.json();
        console.log("Passo 5: Pacote JSON extraído:", dados);
        
        // Garante que o map seja feito corretamente independente da API retornar um array direto ou encapsulado
        const arrayClientes = Array.isArray(dados) ? dados : (dados.dados || []);
        
        if (arrayClientes && arrayClientes.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
            html += '<tr style="background-color: var(--cor-secundaria); border-bottom: 2px solid var(--cor-borda); text-align: left;">';
            html += '<th style="padding: 0.5rem;">ID</th><th style="padding: 0.5rem;">Nome</th><th style="padding: 0.5rem;">CPF</th><th style="padding: 0.5rem;">Status</th><th style="padding: 0.5rem;">Ações</th></tr>';
            
            arrayClientes.forEach(cliente => {
                const status = cliente.ativo == 1 ? 'Ativo' : 'Inativo';
                html += `<tr style="border-bottom: 1px solid var(--cor-borda);">`;
                html += `<td style="padding: 0.5rem;">${cliente.idCliente || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.nome || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${cliente.cpf || '-'}</td>`;
                html += `<td style="padding: 0.5rem;">${status}</td>`;
                html += `<td style="padding: 0.5rem;"><button onclick="alert('Editar ID ${cliente.idCliente}')" style="padding: 0.25rem 0.5rem; cursor: pointer;">Editar</button></td>`;
                html += `</tr>`;
            });
            html += '</table>';
            lista.innerHTML = html;
            console.log("Passo 6: Tabela de clientes renderizada com sucesso no ecrã.");
        } else {
            lista.innerHTML = '<p>Nenhum cliente encontrado na base de dados.</p>';
        }
    } catch (erro) {
        lista.innerHTML = `<p style="color: red;">Erro ao comunicar com a API: Verifique a consola.</p>`;
        console.error("Passo ERRO: Falha ao executar o fetch.", erro);
    }
}