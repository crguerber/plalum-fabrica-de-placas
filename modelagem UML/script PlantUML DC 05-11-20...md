@startuml
skinparam classAttributeIconSize 0
skinparam packageStyle rectangle
skinparam shadowing false
skinparam linetype ortho

package "Boundary (API REST - Fronteira)" {
    class ApiCidade {
        + processarRequisicao()
    }
    class ApiCliente {
        + processarRequisicao()
    }
    class ApiCor {
        + processarRequisicao()
    }
    class ApiPedido {
        + processarRequisicao()
    }
    class ApiTabelaPrecos {
        + processarRequisicao()
    }
    class ApiUf {
        + processarRequisicao()
    }
}

package "Control (Regras de Negócio)" {
    class CtrlCliente {
        + manterCliente(dados)
        + buscarTodos(filtros)
        + atualizarCliente(dados)
        + alterarStatusCliente(dados)
    }
    class CtrlCor {
        + manterCor(dados)
    }
    class CtrlPedido {
        + manterPedido(dados)
        + calcularDataEntrega()
    }
    class CtrlTabelaPrecos {
        + manterPreco(dados)
    }
}

package "Entity (Persistência e Modelo)" {
    class Cidade {
        - id_cidade: int
        - nome: string
        - uf_id: int
        + {static} listarPorUf(uf_id)
    }
    class Cliente {
        - idCliente: int
        - nome: string
        - cpf: string
        - telefone: string
        - email: string
        - ativo: int
        - dataCadastro: date
        - cidade_id: int
        - cep: string
        - logradouro: string
        - numero: string
        - bairro: string
        - tipo_cliente: string
        + inserir()
        + atualizar()
        + {static} listar(filtros)
        + {static} alterarStatus(idCliente, ativo)
    }
    class Conexao {
        + {static} getConexao(): PDO
    }
    class Cor {
        - idCor: int
        - nome: string
    }
    class ItemPedido {
        - idItem: int
        - quantidade: int
        - frase: string
    }
    class Pedido {
        - idPedido: int
        - data_pedido: date
        - data_entrega: date
        - status: string
    }
    class TabelaPrecos {
        - idPreco: int
        - tamanho: string
        - valor: float
    }
    class Uf {
        - uf_id: int
        - sigla: string
        - nome: string
        + {static} listar()
    }
}

' Relacionamentos da Camada Boundary para Control
ApiCliente ..> CtrlCliente : "Encaminha pacote"
ApiCor ..> CtrlCor : "Encaminha pacote"
ApiPedido ..> CtrlPedido : "Encaminha pacote"
ApiTabelaPrecos ..> CtrlTabelaPrecos : "Encaminha pacote"

' Relacionamentos da Camada Boundary Direto para Entity (Casos Read-Only)
ApiCidade ..> Cidade : "Consulta"
ApiUf ..> Uf : "Consulta"

' Relacionamentos da Camada Control para Entity
CtrlCliente ..> Cliente : "Instancia / Valida"
CtrlCor ..> Cor : "Instancia"
CtrlPedido ..> Pedido : "Instancia / Calcula"
CtrlTabelaPrecos ..> TabelaPrecos : "Instancia"

' Associações do Banco de Dados (Dependências entre Entidades)
Cliente "*" --> "1" Cidade : "Localiza-se em"
Cidade "*" --> "1" Uf : "Pertence a"
Pedido "*" --> "1" Cliente : "Realizado por"
Pedido "1" *-- "*" ItemPedido : "Composto por"
ItemPedido "*" --> "1" Cor : "Possui"
ItemPedido "*" --> "1" TabelaPrecos : "Baseado no tamanho"

' Centralização da Conexão
Cidade ..> Conexao : "Usa"
Cliente ..> Conexao : "Usa"
Uf ..> Conexao : "Usa"
Cor ..> Conexao : "Usa"
Pedido ..> Conexao : "Usa"
TabelaPrecos ..> Conexao : "Usa"

@enduml