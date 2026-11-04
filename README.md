# PH Gráfica – Gestão

Sistema de gestão para uma gráfica rápida: controla os pedidos do momento em que chegam até a
entrega, os pagamentos de cada pedido, os gastos do mês e quanto sobrou de lucro.

Feito só com PHP: sem JavaScript, sem framework, sem Composer e sem banco de dados.

## Como rodar

Precisa de PHP 8.1 ou mais novo.

```bash
git clone <url-do-repositorio>
cd ph-grafica
php -S 127.0.0.1:8091
```

Abra http://127.0.0.1:8091.

Na primeira vez o sistema cria seis meses de dados de exemplo (pedidos, pagamentos e gastos
fictícios), com datas calculadas a partir do dia atual. Para começar do zero, apague a pasta
`data/`; ela é recriada no próximo acesso.

Também roda em qualquer hospedagem PHP comum: basta enviar os arquivos e garantir que o PHP possa
escrever na pasta `data/`.

## Telas

- **Painel**: quanto entrou, quanto foi gasto e o lucro do mês, quanto falta receber, quantos
  pedidos estão novos, pendentes e prontos, gráfico de entradas e gastos dos últimos 6 meses,
  próximos prazos e os pedidos esperando o cliente buscar.
- **Pedidos**: quadro com as etapas **Novos → Pendentes → Prontos p/ entrega → Entregues**. Cada
  cartão mostra o prazo (atrasado, hoje, amanhã), o total e quanto falta pagar, e tem o botão da
  próxima etapa.
- **Pedido**: andamento, itens, pagamentos, observações, editar e excluir.
- **Novo pedido / Editar**: cliente, telefone, prazo de entrega, itens (produto, quantidade e preço),
  sinal recebido e observações.
- **Gastos**: lançamento rápido (data, categoria, descrição, valor), lista do mês com total e gastos
  por categoria. Dá para navegar entre os meses.
- **Lucros**: entrou, gastos, lucro e margem do mês escolhido, gráfico dos 6 meses, tabela mês a mês
  e para onde foi o dinheiro.

O tema escuro acompanha a configuração do sistema operacional.

## Como funciona

### Etapas do pedido

| Etapa | Botão para avançar |
|---|---|
| Novo | Começar produção |
| Pendente (em produção) | Finalizar |
| Pronto p/ entrega | Entregar |
| Entregue | |

Ao entregar um pedido que ainda tem saldo, o sistema pergunta quanto o cliente está pagando
naquele momento. Se o valor for inválido (por exemplo, maior que o saldo), o pedido não muda de
etapa.

### Itens e preços

O total do pedido é sempre calculado no servidor a partir dos itens. A gráfica tem uma tabela de
preços (cartão de visita, panfleto, banner, adesivo, fachada em ACM, convite, etc.): escolhendo um
produto da tabela e deixando o preço em branco, o preço da tabela é usado ao salvar.

O formulário começa com três linhas de item; o botão "Mais linhas" acrescenta outras sem perder o
que já foi digitado. Valores aceitam vírgula (`60,50`).

### Pagamentos

O sinal pode ser lançado ao criar o pedido, e os outros pagamentos depois, na página do pedido. Não
dá para receber mais do que falta.

### Lucro

É calculado pelo caixa:

- **Entrou** = soma dos pagamentos recebidos no mês;
- **Gastos** = soma dos gastos lançados no mês;
- **Lucro** = entrou − gastos;
- **Margem** = lucro ÷ entrou.

Por isso, no começo do mês, quando aluguel e salários já foram pagos e ainda entrou pouco, o lucro
pode aparecer negativo.

**A receber** é a soma do que falta pagar em todos os pedidos.

## Decisões técnicas

- **Só PHP.** Cada tela é montada no servidor e cada ação é um formulário. Depois de gravar, a
  página redireciona (POST → redirect → GET), então atualizar a página não grava de novo.
- **Gráfico sem biblioteca.** É um SVG gerado pelo PHP (`views/_chart.php`). Ao passar o mouse sobre
  um mês, o navegador mostra os valores. As cores de entradas e gastos foram escolhidas para serem
  distinguíveis também por quem tem daltonismo, nos dois temas.
- **Dados em arquivos PHP.** Cada coleção fica em `data/<nome>.php`, um arquivo que só devolve um
  array. A gravação usa uma trava (`flock`) e troca atômica (grava num arquivo temporário e
  renomeia), para dois acessos ao mesmo tempo não corromperem o arquivo. Se alguém abrir esses
  arquivos pelo navegador, não aparece nada.
- **Segurança básica.** Todo formulário leva um token CSRF da sessão. Os dados recebidos são
  validados no servidor. Todo texto vindo do usuário é escapado antes de ir para a tela.
- **Trocar o armazenamento.** `src/Store.php` tem métodos simples (`all`, `find`, `insert`,
  `update`, `delete`). Para usar MySQL ou SQLite, basta reescrever essa classe.

## Estrutura

```
index.php            recebe os formulários e escolhe a tela
src/
  Shop.php           validação e gravação de pedidos, pagamentos e gastos
  Reports.php        entradas, gastos, lucro e saldo a receber
  Store.php          gravação dos dados em data/*.php
  Seeder.php         dados de exemplo, tabela de preços, categorias e formas de pagamento
  helpers.php        formatação, etapas do pedido, CSRF, mensagens, logo
views/
  layout.php         estrutura comum (menu)
  painel.php, pedidos.php, pedido.php, novo.php, editar.php, gastos.php, lucros.php
  _chart.php         gráfico de entradas e gastos
  _estilo.php        estilo das telas
data/                criada pelo sistema (não versionar)
```

A tabela de preços, as categorias de gasto e as formas de pagamento ficam em
`Seeder::settings()` (`src/Seeder.php`).

## Limitações

- Não tem login nem controle de usuários.
- A tabela de preços só é alterada pelo código.
- Ainda não gera orçamento ou recibo em PDF.
