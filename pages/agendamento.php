<?php
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/agendamento.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Agendamento de Manicure</title> <!-- Título da Página -->

        <!-- Importando fontes -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
        href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Whisper&display=swap"
        rel="stylesheet">
    </head>

    <body>
        <header>
            <img class="logo" src="../assets/logo.png" width="135px" height="85px">
            <a href="../index.php">Inicio</a>
            <a href="servicos.php">Serviços</a>
            <a href="faleconosco.php">Fale conosco</a>
            <a href="sobrenos.php">Sobre Nós</a>

            <!-- Mostrar páginas diferentes dependendo se você estiver logado ou não -->
            <?php if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_eh_admin'])) { ?>
                <a href="adminDashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php } elseif (isset($_SESSION['usuario_id'])) { ?>
                <a href="agendamento.php">Agendar</a>
                <a href="meusAgendamentos.php">Meus Agendamentos</a>
                <a href="logout.php">Logout</a>
            <?php } else { ?>
                <a href="cadastro.php">Cadastro</a>
                <a href="login.php">Login</a>
            <?php } ?>
        </header>
        <h1>Agendamento</h1>

        <fieldset>
            <form id="formulario-agendamento">
                <label for="servico">Serviço:</label>
                <select id="servico" name="servico" required>
                    <option value="">Selecione um serviço</option>
                </select>

                <div class="input-container">
                    <label for="data-agendamento">Data:</label>
                    <input type="date" id="data-agendamento" placeholder="dd-mm-aaaa" value="" min="2024-12-01" max="2030-12-31" required><br>
                    
                    <label for="hora-agendamento">Horário:</label>
                    <select id="hora-agendamento" required>
                        <!-- Opções serão geradas dinamicamente -->
                    </select>
                </div>

                <label for="observacoes">Observações:</label><br>
                <textarea name="observacoes" id="observacoes" placeholder="Deixe um comentário..." rows="4" cols="50"></textarea>

                <button type="submit">Agendar</button>
            </form>

            <div id="mensagem-erro" class="erro"></div>
        </fieldset>

        <script>
            // Carregar serviços
            function carregarServicos() {
                fetch('../backend.php?acao=listar_servicos')
                    .then(response => response.json())
                    .then(servicos => {
                        const selectServicos = document.getElementById('servico');
                        servicos.forEach(servico => {
                            const option = document.createElement('option');
                            option.value = servico.id;
                            option.textContent = `${servico.nome} - R$ ${servico.preco}`;
                            selectServicos.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao carregar serviços:', error);
                    });
            }

            // Gerar horários disponíveis
            function gerarHorariosDisponiveis() {
                const horariosSelect = document.getElementById('hora-agendamento');
                const diasSemana = [0, 1, 2, 3, 4]; // Segunda a Sexta
                const horariosSemana = [ // Horários de Funcionamento durante a semana
                    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', 
                    '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', 
                    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', 
                    '17:00', '17:30'
                ];

                const horariosSabado = [ // Horários de Funcionamento no Sábado
                    '10:00', '10:30', '11:00', '11:30', 
                    '12:00', '12:30', '13:00', '13:30', 
                    '14:00', '14:30'
                ];

                // Limpar opções anteriores
                horariosSelect.innerHTML = '<option value="">Selecione um horário</option>';

                // Adicionar event listener para atualizar horários quando a data mudar
                document.getElementById('data-agendamento').addEventListener('change', function() {
                    const dataSelecionada = new Date(this.value);
                    const diaSemana = dataSelecionada.getDay();

                    // Limpar opções anteriores
                    horariosSelect.innerHTML = '<option value="">Selecione um horário</option>';

                    // Definir horários baseado no dia da semana
                    const horariosDisponiveis = diaSemana === 5 ? horariosSabado : horariosSemana;

                    // Pedir para inserir outra data foi inserido um Domingo
                    if (diaSemana === 6) {
                        alert('Não funcionamos aos Domingos. Por favor, selecine outra data.');
                        return;
                    }

                    horariosDisponiveis.forEach(horario => {
                        const option = document.createElement('option');
                        option.value = horario;
                        option.textContent = horario;
                        horariosSelect.appendChild(option);
                    });
                });
            }


            // Criar agendamento
            document.getElementById('formulario-agendamento').addEventListener('submit', function (e) {
                e.preventDefault();

                const data = document.getElementById('data-agendamento').value;
                const hora = document.getElementById('hora-agendamento').value;

                if (!data || !hora) {
                    alert('Por favor, selecione data e horário');
                    return;
                }

                // console.log("Data Enviada: " + data);
                // console.log("Horário Enviado: " + hora);

                // Combinar data e hora
                // Formatar a data manualmente sem conversão para ISO
                const dataAgendamento = `${data} ${hora}:00`;

                // console.log("Data Agendada: " + dataAgendamento);

                const formData = new FormData();
                formData.append('acao', 'criar_agendamento');
                formData.append('usuario_id', <?php echo $_SESSION["usuario_id"] ?>);
                formData.append('servico_id', document.getElementById('servico').value);
                formData.append('data_agendamento', dataAgendamento);
                if(document.querySelector("#observacoes").value != ""){
                    formData.append('observacoes', document.querySelector("#observacoes").value);
                }

                console.log(formData);
                
                fetch('../backend.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.sucesso) {
                        alert(resultado.sucesso);
                        window.location.href = 'meusAgendamentos.php';
                    } else {
                        alert(resultado.erro);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
            });

            // Carregar serviços ao inciar
            carregarServicos();

            // Gerar horários ao mudar data
            gerarHorariosDisponiveis();
        </script>

    </body>
</html>