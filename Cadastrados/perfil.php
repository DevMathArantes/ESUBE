<?php 
include_once "../BackEnd/sessao.php";
requiredLogin();
include_once "../BackEnd/conexao.php";
$db = new Conexao();
$result = $db->executar("SELECT cpf , dt_NASC, dt_registro, email FROM usuarios WHERE ra = '".getIdRa()."';");
$cargo = $db->executar("SELECT t.descricao FROM tipo as t JOIN usuarios as u ON t.id = u.tipo WHERE ra = '".getIdRa()."';")[0][0];
$ra = getIdRa();
$nome = getNome();
$cpf = $result[0]['cpf'];
$dtNasc = $result[0]['dt_NASC'];
$idade = (new DateTime($dtNasc))->diff(new DateTime(date('d-m-Y')))->format('%Y anos');
$dtRegistro = date_format(date_create($result[0]['dt_registro']),'d-m-Y \A\s h:i:s A');
$email = $result[0]['email'];
//Para Alunos
if(getPermission() == PERMISSION_ALUNO){
    $turma = $db->executar("SELECT desc_turma FROM view_alunos WHERE ra = '".getIdRa()."';")[0][0];
}
//Menssagens
if(isset($_GET['sucess'])){
    switch($_GET['sucess']){
        case 1:
            msg(MSG_POSITIVE_BG, "Email alterado com sucesso!","msgPopUp msgMargin",null,null,1500);
            break;
        case 2:
            msg(MSG_POSITIVE_BG, "Senha alterada com sucesso!","msgPopUp msgMargin",null,null,1500);
            break;
        default:
            msg(MSG_POSITIVE_BG, "Operação concluída com sucesso!","msgPopUp msgMargin",null,null,1500);
    }
}
if(isset($_GET['senhaInvalida']))
    msg(MSG_NEGATIVE_BG, "Senha inválida!","msgPopUp msgMargin",null,null,1500);

if(isset($_GET['falhaUpdate'])){
    switch($_GET['falhaUpdate']){
        case 1:
            msg(MSG_NEGATIVE_BG, "Falha em alterar os dados no banco!","msgPopUp msgMargin",null,null,1500);
            break;
        case 2:
            if(isset($_GET['restored']))
                msg(MSG_NEGATIVE_BG, "Operação revertida! Falha em alterar os dados no banco!","msgPopUp msgMargin",null,null,1500);
            else
                msg(MSG_NEGATIVE_BG, "Falha em alterar os dados no banco!","msgPopUp msgMargin",null,null,1500);
            break;
        default:

    }
}
if(isset($_GET['falhaEncrypt']))
    msg(MSG_NEGATIVE_BG, "Falha em criptografar a senha!","msgPopUp msgMargin",null,null,1500);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?= getNome(); ?></title>
    <script src="../BackEnd/script.js"></script>
    <link rel="stylesheet" href="../index.css">
</head>
<script>
    //Script somente dessa pagina
    var msg;
    var novoEmail = null;
    var novaSenha = null;
    function trocarEmail(etapa = 1){
        switch(etapa){
            case 1:
                msg = new MsgBox();
                msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_INPUT, _menssagem: "Email atual: "+'<?php echo $email ?>', _title: "Mudar email", _inputPlaceholder: "Novo email, exemple@ex.com", _btnOkName: "Ok", _btnOkAction: "trocarEmail(2);", _btnCancelName: "Cancelar", _autoDestroy: true});
                break;
            case 2:
                if(!validaEmail(msg.returnInput)){
                    msg = new MsgBox();
                    msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_TEXT, _title: "Email inválido!", _btnOkName: "Ok", _autoDestroy: true});
                    break;
                }
                novoEmail = msg.returnInput;
                msg = new MsgBox();
                msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_INPUT, _title: "Confirme sua senha:", _inputPlaceholder: "Senha", _inputPassword: true, _btnOkName: "Confirmar", _btnOkAction: "trocarEmail(3);", _btnCancelName: "Cancelar", _autoDestroy: true});
                break;
            case 3:
                //redirectPOSTAjax("../BackEnd/ProcessTrocaEmailSenha.php","tipoTroca=1&nEmail="+novoEmail+"&senha="+msg.returnInput+"&rUrl="+window.location.href);
                redirectPOST("../BackEnd/ProcessTrocaEmailSenha.php",[["tipoTroca","1"],["nEmail",novoEmail],["senha", msg.returnInput],["rUrl",window.location.href]]);
                msg.destroy();
                break;
        }
    }

    function trocarSenha(etapa = 1){
        switch(etapa){
            case 1:
                msg = new MsgBox();
                msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_INPUT, _title: "Trocar senha", _inputPlaceholder: "Nova senha", _inputPassword: true, _btnOkName: "Ok", _btnOkAction: "trocarSenha(2);", _btnCancelName: "Cancelar", _autoDestroy: true});
                break;
            case 2:
                if(!validaSenha(msg.returnInput)){
                    msg = new MsgBox();
                    msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_TEXT, _title: "Senha inválida!", _menssagem: "A senha deve conter pelo menos 8 caracteres e incluir caracteres especiais.", _btnOkName: "Ok", _autoDestroy: true});
                    break;
                }
                novaSenha = msg.returnInput;
                msg = new MsgBox();
                msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_INPUT, _title: "Confirme a Nova senha:", _inputPlaceholder: "Confirmar Senha", _inputPassword: true, _btnOkName: "Confirmar", _btnOkAction: "trocarSenha(3);", _btnCancelName: "Cancelar", _autoDestroy: true});
                break;
            case 3:
                if(novaSenha != msg.returnInput){
                    msg = new MsgBox();
                    msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_TEXT, _title: "Senha inválida!", _menssagem: "As senhas são diferentes!", _btnOkName: "Ok", _autoDestroy: true});
                    break;
                }
                msg = new MsgBox();
                msg.showInLine({_idName: "msg1", _type: msg.SET_TYPE_INPUT, _title: "Digite sua senha atual:", _inputPlaceholder: "Senha Atual", _inputPassword: true, _btnOkName: "Confirmar", _btnOkAction: "trocarSenha(4);", _btnCancelName: "Cancelar", _autoDestroy: true});
                break;
            case 4:
                //redirectPOSTAjax("../BackEnd/ProcessTrocaEmailSenha.php","troca=1&nEmail="+novoEmail+"&senha="+msg.returnInput+"&rUrl="+window.location.href);
                redirectPOST("../BackEnd/ProcessTrocaEmailSenha.php",[["tipoTroca","2"],["nSenha",novaSenha],["senha", msg.returnInput],["rUrl",window.location.href]]);
                msg.destroy();
                break;
        }
    }
</script>
<body>
    <div id="perf">
        <div id="opc">
            <img src="../Imgs/usuario.png" alt="iconPerfil">
            <button id="trocaEmail" onclick="trocarEmail();">Mudar Email</button>
            <button id="trocaSenha" onclick="trocarSenha();">Trocar Senha</button>
        </div>
        <div id="inf">
            <h2><?= getNome(); ?></h2>
            <h3><?= $cargo?></h3>
            <p><?= (getPermission() == PERMISSION_ALUNO)?"RA":"ID"; ?>: <?= getIdRa();?></p>
            <P>CPF: <?= $db->executar("SELECT CPF FROM usuarios WHERE ra = '".getIdRa()."';")[0][0]; ?></P>
            <p>Data de Nascimento: <?= $dtNasc?></p>
            <p>Idade: <?= $idade?></p>
            <p>Data de <?= (getPermission() == PERMISSION_ALUNO)?"Matricula":"Contratação"; ?>: <?= $dtRegistro?></p>
            <p>Email: <?= $email?></p>
            <?= (getPermission() == PERMISSION_ALUNO)?"<p>Turma: $turma</p>":"";?>
        </div> 
    </div>
</body>
</html>