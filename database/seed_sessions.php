<?php
declare(strict_types=1);

require_once __DIR__ . '/connection.php';

try {
    $db = getDB();
    
    // Iniciar transação de segurança
    $db->beginTransaction();

    // 1. Limpar sessões antigas para não acumular lixo
    $db->exec('DELETE FROM enrollment');
    $db->exec('DELETE FROM class_session');

    // 2. Buscar as aulas que já existem na BD
    $classes = $db->query('SELECT id FROM class')->fetchAll(PDO::FETCH_COLUMN);

    if (empty($classes)) {
        die("<h1 style='color:red;'>❌ Erro: Não tens aulas na base de dados. Publica 2 ou 3 aulas primeiro!</h1>");
    }

    // 3. Gerar um horário dinâmico para os próximos 7 dias
    $rooms = ['Estúdio 1', 'Estúdio Zen', 'Zona de Pesos', 'Ringue', 'Estúdio de Cycling'];
    
    // Blocos de horário disponíveis
    $timeBlocks = [
        ['08:00', '09:00'],
        ['10:30', '11:30'],
        ['13:00', '13:45'],
        ['17:30', '18:30'],
        ['19:00', '20:00']
    ];

    $insertSession = $db->prepare('
        INSERT INTO class_session (class_id, session_date, start_time, end_time, room) 
        VALUES (?, ?, ?, ?, ?)
    ');

    // Para cada aula, vamos criar 2 sessões por dia, durante uma semana
    foreach ($classes as $classId) {
        for ($day = 0; $day <= 6; $day++) {
            $date = date('Y-m-d', strtotime("+$day days"));
            
            // Baralhar os horários para não ser tudo à mesma hora
            shuffle($timeBlocks);
            $room = $rooms[array_rand($rooms)];

            // Sessão da manhã/tarde
            $insertSession->execute([$classId, $date, $timeBlocks[0][0], $timeBlocks[0][1], $room]);
            // Sessão ao final do dia
            $insertSession->execute([$classId, $date, $timeBlocks[1][0], $timeBlocks[1][1], $room]);
        }
    }

    $db->commit();
    echo "<h1 style='color: green; font-family: sans-serif;'>✅ Horário Gerado com Sucesso!</h1>";
    echo "<p style='font-family: sans-serif;'>Foram criadas dezenas de sessões distribuídas pelos próximos 7 dias.</p>";
    echo "<a href='pages/classes.php' style='padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px; font-family: sans-serif;'>Ver Aulas</a>";

} catch (Exception $e) {
    $db->rollBack();
    echo "<h1>❌ Ocorreu um erro:</h1><p>" . $e->getMessage() . "</p>";
}