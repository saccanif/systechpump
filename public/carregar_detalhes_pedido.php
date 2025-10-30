<?php
session_start();
require_once "../config/connection.php";
$conexao = conectarBD();

if (isset($_GET['id'])) {
    $idPedidos = $_GET['id'];
    
    // Query única que funciona para todos os usuários
    $sql_pedido = "SELECT p.*, l.nomeLoja, 
                          u.nomeUsuario as nome_lojista,
                          rep.nomeUsuario as nome_representante, 
                          rep.emailUsuario as email_representante
                   FROM pedidos p 
                   INNER JOIN lojas l ON p.lojas_idlojas = l.idlojas 
                   LEFT JOIN usuario u ON l.idlojas = u.loja_id AND u.tipoUsuario = 'lojista'
                   INNER JOIN usuario rep ON p.representante_id = rep.idUsuario
                   WHERE p.idPedidos = ?";
    
    $stmt_pedido = mysqli_prepare($conexao, $sql_pedido);
    mysqli_stmt_bind_param($stmt_pedido, "i", $idPedidos);
    mysqli_stmt_execute($stmt_pedido);
    $pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pedido));
    
    if ($pedido) {
        echo '<div class="pedido-info">';
        echo '<div class="row mb-3">';
        echo '<div class="col-md-6"><strong>Loja:</strong> ' . htmlspecialchars($pedido['nomeLoja']) . '</div>';
        echo '<div class="col-md-6"><strong>Lojista:</strong> ' . htmlspecialchars($pedido['nome_lojista'] ?? 'Não informado') . '</div>';
        echo '</div>';
        
        echo '<div class="row mb-3">';
        echo '<div class="col-md-6"><strong>Representante:</strong> ' . htmlspecialchars($pedido['nome_representante']) . '</div>';
        echo '<div class="col-md-6"><strong>Email do Representante:</strong> ' . htmlspecialchars($pedido['email_representante']) . '</div>';
        echo '</div>';
        
        echo '<div class="row mb-3">';
        echo '<div class="col-md-6"><strong>Data do Pedido:</strong> ' . date('d/m/Y H:i', strtotime($pedido['dataPedido'])) . '</div>';
        echo '<div class="col-md-6"><strong>Status:</strong> <span class="status-badge status-' . $pedido['status'] . '">';
        
        $statusText = [
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'cancelado' => 'Cancelado'
        ];
        echo $statusText[$pedido['status']] ?? $pedido['status'];
        
        echo '</span></div>';
        echo '</div>';
        
        echo '<div class="row mb-3">';
        echo '<div class="col-md-6"><strong>ID do Pedido:</strong> #' . $pedido['idPedidos'] . '</div>';
        echo '<div class="col-md-6"><strong>Representante ID:</strong> ' . $pedido['representante_id'] . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Buscar itens do pedido - CORREÇÃO: usando a tabela correta
        $sql_itens = "SELECT pi.*, p.nomeProduto, p.categoria, p.imagem_url 
                     FROM pedido_itens pi 
                     INNER JOIN produtos p ON pi.produtos_idProdutos = p.idprodutos 
                     WHERE pi.pedidos_idPedidos = ?";
        $stmt_itens = mysqli_prepare($conexao, $sql_itens);
        mysqli_stmt_bind_param($stmt_itens, "i", $idPedidos);
        mysqli_stmt_execute($stmt_itens);
        $itens = mysqli_stmt_get_result($stmt_itens);
        
        echo '<div class="itens-pedido mt-4">';
        echo '<h6><i class="fas fa-boxes me-2"></i>Itens do Pedido:</h6>';
        
        if (mysqli_num_rows($itens) > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-striped">';
            echo '<thead class="table-light">';
            echo '<tr>';
            echo '<th>Produto</th>';
            echo '<th>Categoria</th>';
            echo '<th class="text-center">Quantidade</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $total_itens = 0;
            while ($item = mysqli_fetch_assoc($itens)) {
                echo '<tr>';
                echo '<td>';
                echo '<div class="d-flex align-items-center">';
                if (!empty($item['imagem_url'])) {
                    echo '<img src="' . htmlspecialchars($item['imagem_url']) . '" 
                              alt="' . htmlspecialchars($item['nomeProduto']) . '" 
                              class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
                } else {
                    echo '<div class="me-2" style="width: 40px; height: 40px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">';
                    echo '<i class="fas fa-box text-muted"></i>';
                    echo '</div>';
                }
                echo '<div>';
                echo '<div class="fw-semibold">' . htmlspecialchars($item['nomeProduto']) . '</div>';
                echo '</div>';
                echo '</div>';
                echo '</td>';
                echo '<td><span class="badge bg-secondary">' . htmlspecialchars($item['categoria']) . '</span></td>';
                echo '<td class="text-center"><span class="badge bg-primary fs-6">' . $item['quantPedItens'] . '</span></td>';
                echo '</tr>';
                $total_itens += $item['quantPedItens'];
            }
            
            echo '</tbody>';
            echo '<tfoot class="table-light">';
            echo '<tr>';
            echo '<td colspan="2" class="text-end"><strong>Total de Itens:</strong></td>';
            echo '<td class="text-center"><strong><span class="badge bg-success fs-6">' . $total_itens . '</span></strong></td>';
            echo '</tr>';
            echo '</tfoot>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">';
            echo '<i class="fas fa-exclamation-triangle me-2"></i>';
            echo 'Nenhum item encontrado para este pedido.';
            echo '</div>';
        }
        echo '</div>';
        
        // Ações para representante
        if (isset($_SESSION['usuario']) && $_SESSION['usuario']['tipoUsuario'] === 'representante' && $pedido['status'] !== 'entregue' && $pedido['status'] !== 'cancelado') {
            echo '<div class="pedido-actions mt-4">';
            echo '<h6><i class="fas fa-cogs me-2"></i>Ações:</h6>';
            echo '<div class="d-flex gap-2">';
            echo '<button class="btn btn-sm btn-outline-primary" onclick="abrirModalAtualizarStatus(' . $pedido['idPedidos'] . ', \'' . $pedido['status'] . '\')">';
            echo '<i class="fas fa-sync-alt me-1"></i>Atualizar Status';
            echo '</button>';
            echo '</div>';
            echo '</div>';
            
            // Script para o modal de atualização de status
            echo '<script>
                function abrirModalAtualizarStatus(idPedidos, statusAtual) {
                    document.getElementById(\'edit_idPedidos\').value = idPedidos;
                    document.getElementById(\'novoStatus\').value = statusAtual;
                    abrirModal(\'atualizarStatusModal\');
                }
                
                function abrirModal(modalId) {
                    document.getElementById(modalId).style.display = \'block\';
                }
            </script>';
        }
        
    } else {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-exclamation-circle me-2"></i>';
        echo 'Pedido não encontrado.';
        echo '</div>';
    }
    
    mysqli_close($conexao);
} else {
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-circle me-2"></i>';
    echo 'ID do pedido não especificado.';
    echo '</div>';
}
?>