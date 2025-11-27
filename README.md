# Sistema de Lavagem de Veículos

Sistema completo de agendamento online para lavagem de veículos desenvolvido em PHP com MySQL.

##  Características Principais

### Funcionalidades do Site
- Catálogo de serviços com preços por tipo de veículo (Moto, Carro, Camioneta)
- Sistema de carrinho de compras
- Agendamento online com seleção de data e hora
- Seleção de forma de pagamento: PIX, Dinheiro ou Cartão
- Cadastro e login de clientes
- Área do cliente para visualizar agendamentos
- Design responsivo e moderno

### Funcionalidades Administrativas
-  Dashboard com indicadores (receita mensal/semanal, lavagens realizadas, etc.)
-  Gerenciamento de Serviços (CRUD completo)
-  Gerenciamento de Clientes (CRUD completo)
-  Gerenciamento de Agendamentos (CRUD completo)
-  Gerenciamento de Usuários (CRUD completo)
-  Relatórios de serviços mais solicitados
-  Controle de status de agendamentos
-  Visualização da forma de pagamento de cada agendamento

### Requisitos Técnicos Atendidos

#### Arquitetura e Padrões
-  **MVC**: Estrutura completa com Models, Views e Controllers separados
-  **Template**: Sistema de layouts reutilizáveis (header/footer)
-  **Manutenibilidade**: Código organizado e comentado

#### Banco de Dados
-  **4 CRUDs completos**: Clientes, Serviços, Agendamentos, Usuários
-  **Triggers**: Sistema de auditoria automática para alterações de preços
-  **Procedures**: Inserção massiva de dados em múltiplas tabelas
-  **Functions**: Verificação de disponibilidade e cálculo automático de valores
-  **Índices**: Otimização de consultas em tabelas com grande volume
-  **Views**: Dashboard agregado para relatórios
 -  **Pagamentos**: Colunas adicionadas em `agendamentos` (`forma_pagamento`, `pagamento_confirmado`, `data_pagamento`) e tabela `pagamentos_pix` para registrar cobranças PIX

#### Funcionalidades
-  **Sistema de Acesso**: Login separado para clientes e administradores
-  **Cadastro de Clientes**: Formulário completo com validações
-  **Carrinho de Compras**: Adição/remoção de serviços antes do agendamento
-  **Dashboard Completo**: Indicadores mensais, semanais e serviços mais solicitados


##  Credenciais Padrão

### Administrador
- **Email:** admin@lavagem.com
- **Senha:** admin123

### Cliente de Teste
- **Email:** joao@email.com
- **Senha:** 123456

## Estrutura do Projeto


E-COMMERCE-main/
├── app/
│   ├── controllers/
│   │   ├── AgendamentosController.php
│   │   ├── BaseController.php
│   │   ├── ClientesController.php
│   │   ├── ServicosController.php
│   │   └── UsuariosController.php
│   ├── models/
│   │   ├── Agendamento.php
│   │   ├── Cliente.php
│   │   ├── Servico.php
│   │   └── Usuario.php
│   └── views/
│       ├── admin/
│       │   ├── header_admin.php
│       │   └── footer_admin.php
│       └── layouts/
│           ├── header.php
│           └── footer.php
├── config/
│   ├── config.php
│   ├── database.php
│   └── pagamento.php
├── database/
│   ├── schema.sql
│   └── adicionar_pagamento.sql
├── public/
│   ├── admin/
│   │   ├── index.php
│   │   ├── dashboard.php
│   │   ├── clientes.php
│   │   ├── servicos.php
│   │   ├── agendamentos.php
│   │   ├── usuarios.php
│   │   └── logout.php
│   ├── api/
│   │   ├── verificar_disponibilidade.php
│   │   └── verificar_pagamento.php
│   ├── css/
│   │   └── style.css
│   ├── index.php
│   ├── login.php
│   ├── cadastro.php
│   ├── agendamento.php
│   ├── meus-agendamentos.php
│   ├── pagamento-pix.php
│   └── logout.php
└── README.md
```

## Instalação e Execução

1. Instale e inicie `Apache` e `MySQL` (XAMPP recomendado).
2. Crie o banco `lavagem_veiculos` e importe `database/schema.sql`.
3. Execute também `database/adicionar_pagamento.sql` para criar as colunas e tabela de pagamentos.
4. Ajuste credenciais em `config/database.php` (host, usuário, senha).
5. Configure o DocumentRoot do servidor para apontar para `public/` ou copie o projeto para `htdocs` e acesse pela URL.
6. Acesse `http://localhost/public/index.php` (página inicial) ou configure um virtual host para uma URL amigável.

### URLs Principais
- `public/index.php`: catálogo e carrinho
- `public/agendamento.php`: agendamento
- `public/pagamento-pix.php`: página de pagamento via PIX
- `public/login.php`: login de cliente
- `public/cadastro.php`: cadastro de cliente
- `public/meus-agendamentos.php`: área do cliente
- `public/admin/index.php`: painel administrativo
- `public/api/verificar_disponibilidade.php`: verificação de disponibilidade
- `public/api/verificar_pagamento.php`: verificação de status de pagamento

##  Banco de Dados

### Tabelas Principais
- **usuarios**: Usuários administrativos
- **clientes**: Clientes do sistema
- **categorias_servicos**: Categorias de serviços
- **servicos**: Serviços oferecidos
- **agendamentos**: Agendamentos realizados
- **agendamento_itens**: Itens de cada agendamento
- **auditoria_precos**: Auditoria de alterações de preços

### Triggers
- **trg_auditoria_preco_moto**: Auditoria automática de preços para motos
- **trg_auditoria_preco_carro**: Auditoria automática de preços para carros
- **trg_auditoria_preco_camioneta**: Auditoria automática de preços para camionetas

### Procedures
- **sp_inserir_servicos_massivo**: Inserção em massa de serviços
- **sp_inserir_clientes_massivo**: Inserção em massa de clientes

### Functions
- **fn_verificar_disponibilidade**: Verifica disponibilidade de estoque
- **fn_calcular_valor_servico**: Calcula valor baseado no tipo de veículo

### Views
- **vw_dashboard_semanal**: Dashboard de desempenho semanal
- **vw_servicos_mais_solicitados**: Serviços mais vendidos

## Funcionalidades Detalhadas

### Para Clientes

1. **Navegação de Serviços**
   - Visualização de todos os serviços disponíveis
   - Preços diferenciados por tipo de veículo
   - Sistema de carrinho para múltiplos serviços

2. **Agendamento**
   - Seleção de data e horário
   - Informações do veículo (tipo, placa, modelo)
   - Cálculo automático do valor total
   - Observações adicionais
   - Escolha da forma de pagamento (PIX, Dinheiro, Cartão)
   - Para PIX, geração automática do QR Code (Copia e Cola)

3. **Área do Cliente**
   - Visualização de agendamentos realizados
   - Status de cada agendamento
   - Histórico completo

### Para Administradores

1. **Dashboard**
   - Indicadores de performance
   - Receita mensal e semanal
   - Quantidade de lavagens realizadas
   - Serviços mais solicitados
   - Agendamentos do período

2. **Gestão de Serviços**
   - Criar, editar e excluir serviços
   - Definir preços por tipo de veículo
   - Controlar estoque
   - Ativar/desativar serviços

3. **Gestão de Clientes**
   - Visualizar todos os clientes
   - Dados completos de contato
   - Histórico de agendamentos

4. **Gestão de Agendamentos**
   - Visualizar todos os agendamentos
   - Atualizar status (Pendente → Confirmado → Em Andamento → Concluído)
   - Cancelar agendamentos
   - Visualizar detalhes completos
   - Ver a forma de pagamento selecionada em cada agendamento

5. **Gestão de Usuários**
   - Criar usuários administrativos
   - Definir permissões (Admin/Funcionário)
   - Ativar/desativar usuários

## Segurança

- Senhas criptografadas com MD5
- Proteção contra SQL Injection (PDO com prepared statements)
- Validação de sessões
- Controle de acesso por tipo de usuário
- Sanitização de inputs

##  Relatórios e Indicadores

### Dashboard Administrativo Inclui:
- Total de agendamentos do mês
- Total de lavagens concluídas (mês e semana)
- Receita mensal e semanal
- Total de clientes cadastrados
- Top 5 serviços mais solicitados
- Desempenho semanal detalhado
- Últimos agendamentos realizados

## 🛠 Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Arquitetura**: MVC (Model-View-Controller)
- **Web Server**: Apache (via XAMPP)

##  Notas Importantes

1. O sistema usa `localStorage` para o carrinho de compras
2. Todas as datas seguem o formato brasileiro (dd/mm/yyyy)
3. Valores monetários em Real (R$)
4. Sistema de auditoria registra todas as alterações de preços
5. Funções e procedures facilitam operações em massa

##  Suporte

Para problemas ou dúvidas:
1. Verifique se Apache e MySQL estão rodando no XAMPP
2. Certifique-se de que o banco foi importado corretamente
3. Verifique as configurações em `config/database.php`

##  Licença

Este projeto foi desenvolvido para fins educacionais.

---

**Desenvolvido com PHP + MySQL | Sistema MVC Completo**
