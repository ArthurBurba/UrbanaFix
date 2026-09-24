import mysql.connector
import re
import sys
import traceback
import textwrap
from PyQt5 import uic, QtWidgets, QtCore
from PyQt5.QtWidgets import QMessageBox, QTableWidgetItem


def connect():
    return mysql.connector.connect(
        user='root',
        password='',
        host='localhost',
        port='3306',
        database='urbanafix'
    )


def msg_padrao(tipo, parent, titulo, texto, botoes=QMessageBox.Ok):
    msg = QMessageBox(parent)
    msg.setStyleSheet("")
    msg.setIcon(tipo)
    msg.setWindowTitle(titulo)
    msg.setText(texto)
    msg.setStandardButtons(botoes)
    return msg.exec_()


def excecao_global(tipo, valor, tb):
    erro = ''.join(traceback.format_exception(tipo, valor, tb))
    print(erro)
    msg_padrao(QMessageBox.Critical, None, "Erro fatal", f"Ocorreu um erro:\n\n{erro}")
    sys.__excepthook__(tipo, valor, tb)


sys.excepthook = excecao_global


def validar_tabela(nome):
    return bool(re.match(r'^[A-Za-z0-9_]+$', nome))


def limpar_cadastro():
    tela_cadastro.txt_nome.clear()
    tela_cadastro.txt_email.clear()
    tela_cadastro.txt_senha.clear()


def limpar_consulta():
    tela_consulta.txt_id.clear()
    tela_consulta.txt_email.clear()
    tela_consulta.txt_nome.clear()
    tela_consulta.txt_senha.clear()


def cadastrar_usuario():
    nome = tela_cadastro.txt_nome.text().strip()
    email = tela_cadastro.txt_email.text().strip()
    senha = tela_cadastro.txt_senha.text().strip()

    if not nome:
        msg_padrao(QMessageBox.Warning, tela_cadastro, "Aviso", "Coloque o nome corretamente!")
        return

    try:
        conn = connect()
        cursor = conn.cursor()
        cursor.execute(
            "INSERT INTO usuarios (usuario, email, senha) VALUES (%s,%s,%s)",
            (nome, email, senha)
        )
        conn.commit()
        msg_padrao(QMessageBox.Information, tela_cadastro, "Sucesso", "Cadastro feito com sucesso!")
        limpar_cadastro()
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_cadastro, "Erro", str(e))
    finally:
        cursor.close()
        conn.close()


def buscar():
    id = tela_consulta.txt_id.text().strip()
    try:
        conn = connect()
        cursor = conn.cursor()
        cursor.execute("SELECT email, usuario, senha FROM usuarios WHERE id=%s", (id,))
        res = cursor.fetchone()
        if res:
            tela_consulta.txt_email.setText(res[0])
            tela_consulta.txt_nome.setText(res[1])
            tela_consulta.txt_senha.setText(res[2])
            msg_padrao(QMessageBox.Information, tela_consulta, "Aviso", "Cliente encontrado!")
        else:
            limpar_consulta()
            msg_padrao(QMessageBox.Information, tela_consulta, "Aviso", "Cliente não encontrado!")
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_consulta, "Erro", str(e))
    finally:
        cursor.close()
        conn.close()


def excluir():
    id = tela_consulta.txt_id.text().strip()
    reply = msg_padrao(QMessageBox.Question, tela_consulta, "Confirmação", f"Excluir cliente {id}?",
                       QMessageBox.Yes | QMessageBox.No)
    if reply == QMessageBox.Yes:
        try:
            conn = connect()
            cursor = conn.cursor()
            cursor.execute("DELETE FROM usuarios WHERE id=%s", (id,))
            conn.commit()
            msg_padrao(QMessageBox.Information, tela_consulta, "Sucesso", "Cliente excluído!")
            limpar_consulta()
        except Exception as e:
            msg_padrao(QMessageBox.Critical, tela_consulta, "Erro", str(e))
        finally:
            cursor.close()
            conn.close()


def listar_tabelas():
    try:
        conn = connect()
        cursor = conn.cursor()
        cursor.execute("SHOW TABLES")
        tabelas = [t[0] for t in cursor.fetchall()]
        tela_visualizar.cmb_tabelas.clear()
        tela_visualizar.cmb_tabelas.addItems(tabelas)
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_visualizar, "Erro", f"Erro ao listar tabelas: {e}")
    finally:
        cursor.close()
        conn.close()


def carregar_tabela():
    tabela = tela_visualizar.cmb_tabelas.currentText()
    ordem_selecionada = tela_visualizar.cmb_ordenacao.currentText()

    if not tabela or not validar_tabela(tabela):
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Aviso", "Selecione uma tabela válida")
        return

    try:
        conn = connect()
        cursor = conn.cursor()

        if ordem_selecionada == "Ordenar por Peso":
            cursor.execute(f"""
                SELECT * FROM `{tabela}`
                ORDER BY FIELD(peso, 'pequeno', 'médio', 'grande') DESC
            """)
        else:
            cursor.execute(f"SELECT * FROM `{tabela}` ORDER BY id DESC")

        dados = cursor.fetchall()
        colunas = cursor.column_names

        tela_visualizar.tbl_dados.blockSignals(True)
        tela_visualizar.tbl_dados.setRowCount(len(dados))
        tela_visualizar.tbl_dados.setColumnCount(len(colunas))
        tela_visualizar.tbl_dados.setHorizontalHeaderLabels(colunas)

        for i, linha in enumerate(dados):
            for j, valor in enumerate(linha):
                texto = str(valor)
                texto_formatado = "\n".join(textwrap.wrap(texto, width=40))
                tela_visualizar.tbl_dados.setItem(i, j, QTableWidgetItem(texto_formatado))

        tela_visualizar.tbl_dados.blockSignals(False)

        tela_visualizar.tbl_dados.setWordWrap(True)
        tela_visualizar.tbl_dados.resizeColumnsToContents()
        tela_visualizar.tbl_dados.resizeRowsToContents()
        tela_visualizar.tbl_dados.setHorizontalScrollMode(QtWidgets.QAbstractItemView.ScrollPerPixel)
        tela_visualizar.tbl_dados.setHorizontalScrollBarPolicy(QtCore.Qt.ScrollBarAsNeeded)

        tela_visualizar.tbl_dados.setEditTriggers(QtWidgets.QAbstractItemView.NoEditTriggers)

        msg_padrao(QMessageBox.Information, tela_visualizar, "Carregado", f"{len(dados)} registros carregados.")
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_visualizar, "Erro", f"Erro ao carregar: {e}")
    finally:
        cursor.close()
        conn.close()


def atualizar_celula(item):
    tabela = tela_visualizar.cmb_tabelas.currentText()
    if not tabela:
        return

    tela_visualizar.tbl_dados.blockSignals(True)
    try:
        row = item.row()
        col = item.column()
        novo_valor = item.text().strip()

        colunas = [tela_visualizar.tbl_dados.horizontalHeaderItem(i).text()
                   for i in range(tela_visualizar.tbl_dados.columnCount())]

        if 'id' not in colunas:
            msg_padrao(QMessageBox.Warning, tela_visualizar, "Erro", "A tabela precisa ter uma coluna 'id'.")
            return

        pk_col = colunas.index('id')
        pk_item = tela_visualizar.tbl_dados.item(row, pk_col)
        if not pk_item:
            msg_padrao(QMessageBox.Warning, tela_visualizar, "Erro", "Não foi possível identificar o ID da linha.")
            return

        pk = pk_item.text()
        coluna_editada = colunas[col]

        if coluna_editada == 'id':
            msg_padrao(QMessageBox.Warning, tela_visualizar, "Aviso", "O campo 'id' não pode ser alterado.")
            carregar_tabela()
            return

        conn = connect()
        cursor = conn.cursor()
        cursor.execute(f"UPDATE `{tabela}` SET `{coluna_editada}`=%s WHERE `id`=%s", (novo_valor, pk))
        conn.commit()
        cursor.close()
        conn.close()

        msg_padrao(QMessageBox.Information, tela_visualizar, "Sucesso",
                   f"O campo '{coluna_editada}' do registro {pk} foi atualizado com sucesso!")
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_visualizar, "Erro", f"Erro ao atualizar célula:\n{e}")
    finally:
        tela_visualizar.tbl_dados.blockSignals(False)


def alternar_edicao():
    """Alterna entre modo de edição e confirmação."""
    tabela = tela_visualizar.cmb_tabelas.currentText()
    if not tabela:
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Aviso", "Nenhuma tabela selecionada!")
        return

    if tela_visualizar.tbl_dados.editTriggers() == QtWidgets.QAbstractItemView.NoEditTriggers:
        tela_visualizar.tbl_dados.setEditTriggers(
            QtWidgets.QAbstractItemView.DoubleClicked |
            QtWidgets.QAbstractItemView.SelectedClicked
        )
        tela_visualizar.btn_editar.setText("Confirmar")
        msg_padrao(QMessageBox.Information, tela_visualizar, "Modo edição", "Edição ativada.")
        tela_visualizar.tbl_dados.itemChanged.connect(atualizar_celula)
    else:
        tela_visualizar.tbl_dados.setEditTriggers(QtWidgets.QAbstractItemView.NoEditTriggers)
        tela_visualizar.btn_editar.setText("Editar")
        msg_padrao(QMessageBox.Information, tela_visualizar, "Confirmado", "Edição confirmada e salva.")
        carregar_tabela()


def excluir_registro():
    tabela = tela_visualizar.cmb_tabelas.currentText()
    row = tela_visualizar.tbl_dados.currentRow()
    if row < 0:
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Aviso", "Selecione uma linha")
        return

    colunas = [tela_visualizar.tbl_dados.horizontalHeaderItem(i).text()
               for i in range(tela_visualizar.tbl_dados.columnCount())]
    if 'id' not in colunas:
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Erro", "Essa tabela não possui coluna 'id'")
        return

    pk_col = colunas.index('id')
    pk = tela_visualizar.tbl_dados.item(row, pk_col).text()

    reply = msg_padrao(QMessageBox.Question, tela_visualizar, "Confirmação",
                       f"Deseja excluir registro ID {pk}?", QMessageBox.Yes | QMessageBox.No)

    if reply == QMessageBox.Yes:
        try:
            conn = connect()
            cursor = conn.cursor()
            cursor.execute(f"DELETE FROM `{tabela}` WHERE `id`=%s", (pk,))
            conn.commit()
            carregar_tabela()
            msg_padrao(QMessageBox.Information, tela_visualizar, "Sucesso", "Registro excluído!")
        except Exception as e:
            msg_padrao(QMessageBox.Critical, tela_visualizar, "Erro", f"Erro ao excluir: {e}")
        finally:
            cursor.close()
            conn.close()


def marcar_resolvida():
    tabela = tela_visualizar.cmb_tabelas.currentText()
    row = tela_visualizar.tbl_dados.currentRow()
    if row < 0:
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Aviso", "Selecione uma denúncia para marcar")
        return

    colunas = [tela_visualizar.tbl_dados.horizontalHeaderItem(i).text()
               for i in range(tela_visualizar.tbl_dados.columnCount())]
    if 'id' not in colunas or 'status' not in colunas:
        msg_padrao(QMessageBox.Warning, tela_visualizar, "Erro", "Essa tabela precisa ter colunas 'id' e 'status'")
        return

    pk_col = colunas.index('id')
    pk = tela_visualizar.tbl_dados.item(row, pk_col).text()

    try:
        conn = connect()
        cursor = conn.cursor()
        cursor.execute(f"UPDATE `{tabela}` SET `status`=%s WHERE `id`=%s", ("Resolvida", pk))
        conn.commit()
        msg_padrao(QMessageBox.Information, tela_visualizar, "Sucesso", "Denúncia marcada como resolvida!")
        carregar_tabela()
    except Exception as e:
        msg_padrao(QMessageBox.Critical, tela_visualizar, "Erro", f"Erro ao atualizar: {e}")
    finally:
        cursor.close()
        conn.close()


def abrircadastro():
    tela_cadastro.show()


def abrirconsulta():
    tela_consulta.show()


def abrirvisualizador():
    tela_visualizar.show()
    listar_tabelas()


app = QtWidgets.QApplication([])

tela_principal = uic.loadUi("principal.ui")
tela_cadastro = uic.loadUi("cadastro.ui")
tela_consulta = uic.loadUi("consultar.ui")
tela_visualizar = uic.loadUi("visualizar.ui")

tela_principal.btn_cadastrar.clicked.connect(abrircadastro)
tela_principal.btn_consultar.clicked.connect(abrirconsulta)
tela_principal.btn_visualizar.clicked.connect(abrirvisualizador)

tela_cadastro.btn_cadastrar.clicked.connect(cadastrar_usuario)
tela_cadastro.btn_limpar.clicked.connect(limpar_cadastro)

tela_consulta.btn_consultar.clicked.connect(buscar)
tela_consulta.btn_limpar.clicked.connect(limpar_consulta)
tela_consulta.btn_excluir.clicked.connect(excluir)

tela_visualizar.btn_carregar.clicked.connect(carregar_tabela)
tela_visualizar.btn_excluir.clicked.connect(excluir_registro)
tela_visualizar.btn_resolvida.clicked.connect(marcar_resolvida)
tela_visualizar.btn_editar.clicked.connect(alternar_edicao)

tela_principal.show()
app.exec()
