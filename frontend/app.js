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
async function carregarVista(vista) {
    document.getElementById('navegacao').classList.remove('ativa');
    const conteudoPrincipal = document.getElementById('conteudoPrincipal');
    
    if (vista === 'dashboard') {
        conteudoPrincipal.innerHTML = `<h2>Carregando Painel de Controle...</h2>`;
    } else {
        try {
            const resposta = await fetch(`${vista}.html`);
            if (!resposta.ok) throw new Error('Módulo não encontrado no servidor.');
            
            const html = await resposta.text();
            conteudoPrincipal.innerHTML = html;
            
            if (vista === 'clientes') {
                listarClientes();
            }
        } catch (erro) {
            conteudoPrincipal.innerHTML = `<section class="vista-modulo"><p style="color: red;">Erro ao carregar a interface: ${erro.message}</p></section>`;
        }
    }
}