@startuml
skinparam componentStyle uml2

package "Frontend (Navegador)" as frontend {
    [Interface de Clientes\n(TelaManutençãoCliente)] as UICliente
    [Interface de Pedidos\n(TelaManutençãoPedido)] as UIPedido
    [Interface de Tabela precos\n(TelaManutençãoTabelaPrecos)] as UITabelaPrecos
    [Interface de Cores\n(TelaManutençãoCores)] as UICores
}
note right of frontend
  - HTML5
  - Javascript
  - CSS3
end note

package "Backend (Servidor PHP)" as backend {
    package "Boundary (API REST)" {
        [Rotas de Clientes\n(ApiCliente.php)] as APICliente
        [Rotas de Pedidos\n(ApiPedido.php)] as APIPedido
        [Rotas de Tabela de Precos\n(ApiTabelaPrecos.php)] as APITabelaPrecos
        [Rotas de Cores\n(ApiCor.php)] as APICor
        [Rotas de Cidades\n(ApiCidade.php)] as APICidade
        [Rotas de UFs\n(ApiUf.php)] as APIUf
    }
    
    package "Control" {
        [CtrlCliente.php] as CtrlCliente
        [CtrlPedido.php] as CtrlPedido
        [CtrlTabelaPrecos.php] as CtrlTabelaPrecos
        [CtrlCor.php] as CtrlCor
    }
    
    package "Entity" {
        [Cliente.php] as EntCliente
        [Pedido.php\nItemPedido.php] as EntPedido
        [TabelaPrecos.php] as EntTabelaPrecos
        [Cor.php] as EntCor
        [Cidade.php] as EntCidade
        [Uf.php] as EntUf
        [Conexao.php] as Conexao
    }
}

note right of backend
  - PHP 8.5
  - Composer 2.9
end note

database "PLALUM_DB" as plalum_db
note right of plalum_db
  Persiste os dados nas tabelas: 
          - cliente
          - tabelaprecos
          - cor
          - pedido
          - itempedido
          - cidade
          - uf
end note
note left of plalum_db
    - SGBD MySQL
end note

' Comunicação Frontend -> Boundary (API)
UICliente ..> APICliente : HTTP / JSON
UICliente ..> APICidade : HTTP / JSON
UICliente ..> APIUf : HTTP / JSON
UIPedido ..> APIPedido : HTTP / JSON
UITabelaPrecos ..> APITabelaPrecos : HTTP / JSON
UICores ..> APICor : HTTP / JSON

' Comunicação Boundary -> Control
APICliente ..> CtrlCliente : Encaminha
APIPedido ..> CtrlPedido : Encaminha
APITabelaPrecos ..> CtrlTabelaPrecos : Encaminha
APICor ..> CtrlCor : Encaminha

' Comunicação Boundary -> Entity (Read-only / Sem Controladora)
APICidade ..> EntCidade : Consulta Direta
APIUf ..> EntUf : Consulta Direta

' Comunicação Control -> Entity
CtrlCliente ..> EntCliente : Controla
CtrlPedido ..> EntPedido : Controla
CtrlPedido ..> EntCliente : Consulta
CtrlPedido ..> EntTabelaPrecos : Consulta
CtrlPedido ..> EntCor : Consulta
CtrlTabelaPrecos ..> EntTabelaPrecos : Controla

' Comunicação Entity -> Base de Dados
EntCliente ..> Conexao : Requer
EntPedido ..> Conexao : Requer
EntTabelaPrecos ..> Conexao : Requer
EntCor ..> Conexao : Requer
EntCidade ..> Conexao : Requer
EntUf ..> Conexao : Requer
Conexao ..> plalum_db : Executa SQL

@enduml