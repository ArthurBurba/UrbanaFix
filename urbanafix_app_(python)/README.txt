==============================================================================
GUIA DE INSTALAÇÃO E CONFIGURAÇÃO
Python | PyQt5 | MySQL

[ 1. INSTALAÇÃO DAS DEPENDÊNCIAS ]

Abra o seu terminal (CMD, PowerShell ou Terminal) e execute o comando abaixo:

pip install mysql-connector-python PyQt5

[ 2. VALIDAÇÃO E TESTES ]

Apos concluir a instalacao, rode os comandos a seguir para verificar se tudo
foi instalado corretamente.

  Teste do PyQt5:
  Execute no terminal:

  python -m PyQt5.uic.pyuic

-> Resultado esperado: Nao deve retornar erro de modulo nao encontrado.

---

Teste do MySQL Connector:
Execute no terminal:

python -c "import mysql.connector; print('OK')"

-> Resultado esperado: Deve imprimir "OK" na tela.

==============================================================================