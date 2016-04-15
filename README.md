# teste

Essa é uma aplicação teste desenvolvida para usar alguns recursos da API NFePHP, como se essa fosse a sua aplicação.

Para usar essa aplicação teste, baixe a mesma com o git:

```
git clone https://github.com/robmachado/teste.git
```

Vá para pasta criada `teste`

Na raiz da pasta, pelo terminal digite:

```
composer install
```

Se você não tiver o composer, baixe de `https://getcomposer.org/download/`

O composer irá criar uma pasta vendor com todas as dependências.

Dê permissão de cotrole total na pasta `./base` para o usuário do apache, normalmente pelo comando:

```
chown www-data:www-data base -R
```

Coloque o seu arquivo de configuração `config.json` na pasta config e o seu certificado digital (.pfx) na pasta certs.
Cuidado com os caminhos setados no `config.json`, pode-se usar caminho absoluto ou relativo.
OBS.: No caminho para o certificado e para os webservices coloque somente o nome dos arquivos, sem a pasta.

Se já não tiver sido criado, crie as pastas onde ficarão armazenados os documentos (NFe, CTe, etc)
e as respectivas subpastas e dê permissão total para o usuário do apache.
Essas pastas devem estar consistente com o arquivo `config.json`.

Para usar a aplicação, acesse em um navegador o endereço:

```
http://localhost/teste/publico
```

Ou no servidor/caminho que você configurou no seu apache.

O arquivo `cron/distdfe.php` deve ser executado periodicamente pelo seu cron para realizar a comunicação com a Sefaz.
OBS.: Não coloque frequencia maior do que algumas vezes por dia pois a Sefaz poderá bloquear sua aplicação por abuso no consumo.

E já pode usar.


------------------------

Para atualizar sua instalação para uma versão mais nova da aplicação teste execute:

```
git pull
```

Para atualizar as dependências, inclusive o nfe-php, execute:

```
composer update
```
