<!DOCTYPE html>
<html lang="pt-br">
<head>
    </head>
<body>
    <tbody>
        <?php foreach($alunos as $aluno): ?>
        <tr>
            <td>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-tech-700 flex items-center justify-center text-xs font-bold text-tech-primary">
                        <?= strtoupper(substr($aluno['nome'], 0, 2)) ?>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-white"><?= $aluno['nome'] ?></div>
                        <div class="text-xs text-gray-400"><?= $aluno['email'] ?></div>
                    </div>
                </div>
            </td>
            </tr>
        <?php endforeach; ?>
    </tbody>