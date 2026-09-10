<?php
// Inclui a conexão com o banco de dados
require_once __DIR__ . '/conexao.php';

// Busca informações da seção "Sobre o Polo" cadastradas no Admin, caso deseje complementar
$dados_sobre = [];
try {
    $stmt_sb = $pdo->query("SELECT * FROM sobre_polo WHERE id = 1");
    if ($stmt_sb) {
        $dados_sobre = $stmt_sb->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {}

// Inclui o Cabeçalho Global
require_once __DIR__ . '/header.php';
?>

<style>
    body {
        background-color: #f8f9fa;
        color: #212529;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    }

    /* Estilos específicos da página Quem Somos */
    .header-quemsomos {
        background: linear-gradient(135deg, #111 0%, #1a1a1a 100%);
        color: #fff;
        text-align: center;
        padding: 60px 20px 80px 20px;
    }
    .header-quemsomos h1 { font-size: 2.5em; font-weight: 800; color: #fff; letter-spacing: 1px; }
    .linha-laranja { width: 70px; height: 4px; background-color: #f57c00; margin: 15px auto; border-radius: 2px; }
    .header-quemsomos p { font-size: 1.15em; color: #ccc; max-width: 700px; margin: 0 auto; }

    /* Container Principal */
    .container-quemsomos { max-width: 1100px; margin: -40px auto 60px auto; padding: 0 20px; }

    /* Card Institucional / Introdução */
    .card-intro {
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        margin-bottom: 40px;
        border-top: 5px solid #f57c00;
    }
    .card-intro p { font-size: 1.1em; color: #444; margin-bottom: 20px; text-align: justify; }
    .card-intro p:last-child { margin-bottom: 0; }

    /* Grid de Missão, Visão e Valores */
    .grid-pilares {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    .card-pilar {
        background: #fff;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        transition: transform 0.3s ease;
    }
    .card-pilar:hover { transform: translateY(-5px); }

    .card-pilar.visao { border-left: 5px solid #f57c00; }
    .card-pilar.missao { border-left: 5px solid #111; }

    .card-pilar h2 { font-size: 1.6em; color: #111; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
    .card-pilar p { color: #555; font-size: 1em; text-align: justify; }

    /* Seção de Valores */
    .card-valores {
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        margin-bottom: 40px;
    }
    .card-valores h2 { font-size: 1.8em; color: #111; text-align: center; margin-bottom: 10px; }
    .card-valores .sub-titulo { text-align: center; color: #666; margin-bottom: 30px; }

    .lista-valores {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .item-valor {
        background: #fdfdfd;
        border: 1px solid #eee;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #f57c00;
    }
    .item-valor h3 { font-size: 1.1em; color: #111; margin-bottom: 8px; }
    .item-valor p { font-size: 0.95em; color: #666; }

    /* Banner de Destaque / Manifesto */
    .banner-manifesto {
        background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);
        color: #fff;
        padding: 45px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(245, 124, 0, 0.2);
    }
    .banner-manifesto h3 { font-size: 1.6em; margin-bottom: 15px; font-weight: 800; }
    .banner-manifesto p { font-size: 1.1em; max-width: 900px; margin: 0 auto 25px auto; opacity: 0.95; line-height: 1.8; }
    .btn-contato {
        display: inline-block;
        background: #111;
        color: #fff;
        padding: 14px 30px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s;
    }
    .btn-contato:hover { background: #222; transform: scale(1.05); }

    /* Responsividade */
    @media (max-width: 850px) {
        .grid-pilares { grid-template-columns: 1fr; }
        .header-quemsomos h1 { font-size: 2em; }
        .card-intro, .card-pilar, .card-valores, .banner-manifesto { padding: 25px; }
    }
</style>

<!-- Header Principal -->
<header class="header-quemsomos">
    <h1>QUEM SOMOS</h1>
    <div class="linha-laranja"></div>
    <p>Transformando vidas através de uma educação acessível, inovadora e de alto padrão.</p>
</header>

<main class="container-quemsomos">
    
    <!-- Apresentação Principal -->
    <section class="card-intro">
        <p>A <strong>FENATEHD</strong> nasceu com a convicção de que o conhecimento é a principal chave para a transformação social e profissional. Atuando como uma startup educacional inovadora, nosso propósito vai muito além da formação tradicional: buscamos capacitar, inspirar e abrir caminhos reais para que nossos estudantes conquistem o ensino superior e seu espaço no mercado de trabalho.</p>
    </section>

    <!-- Visão e Missão -->
    <section class="grid-pilares">
        
        <!-- Visão -->
        <article class="card-pilar visao">
            <h2>👁️ Visão</h2>
            <p>Ser uma referência nacional na educação voltada às áreas de dependência química, saúde mental e educação continuada, promovendo transformação social e impacto positivo na vida dos nossos alunos. Queremos ir além dos números, focando na formação de profissionais qualificados e preparados para o mercado de trabalho, contribuindo para o desenvolvimento da nossa categoria e da sociedade.</p>
        </article>

        <!-- Missão -->
        <article class="card-pilar missao">
            <h2>🎯 Missão</h2>
            <p>Nosso compromisso é oferecer educação de qualidade, acessível e inovadora, proporcionando oportunidades reais para nossos alunos alcançarem seus sonhos acadêmicos e profissionais. Como startup educacional, acreditamos que o conhecimento é a chave para a mudança e, por isso, trabalhamos incansavelmente para oferecer cursos acessíveis e de alto padrão, que capacitem e impulsionem nossos estudantes rumo ao ensino superior e ao mercado de trabalho.</p>
        </article>

    </section>

    <!-- Nossos Valores -->
    <section class="card-valores">
        <h2>Nossos Valores</h2>
        <div class="linha-laranja"></div>
        <p class="sub-titulo">Os princípios fundamentais que guiam todas as nossas ações e decisões.</p>

        <div class="lista-valores">
            
            <div class="item-valor">
                <h3>🤝 Compromisso com o aluno</h3>
                <p>Colocamos nossos estudantes no centro de tudo, oferecendo suporte contínuo e formação de alta qualidade.</p>
            </div>

            <div class="item-valor">
                <h3>💡 Acessibilidade</h3>
                <p>Lutamos por uma educação acessível, proporcionando valores justos e oportunidades para todos.</p>
            </div>

            <div class="item-valor">
                <h3>🚀 Inovação</h3>
                <p>Buscamos constantemente novas formas de ensinar e aprender, acompanhando as principais tendências educacionais.</p>
            </div>

            <div class="item-valor">
                <h3>⚖️ Ética e Responsabilidade Social</h3>
                <p>Atuamos com transparência e compromisso na formação de profissionais capacitados e conscientes.</p>
            </div>

            <div class="item-valor">
                <h3>🌱 Transformação de Vidas</h3>
                <p>Nosso maior orgulho é ver nossos alunos alcançando o ensino superior e realizando seus sonhos, fruto da nossa dedicação.</p>
            </div>

        </div>
    </section>

    <!-- Banner Transformação de Vidas -->
    <section class="banner-manifesto">
        <h3>Mais do que capacitar, nós transformamos vidas!</h3>
        <p>Hoje, nos sentimos honrados em ver nossos alunos ingressando na faculdade e conquistando seus espaços no ensino superior, fruto de nossa luta por uma educação de qualidade e acessível. A FENATEHD torna possível o sonho da graduação para muitos que antes viam essa meta como inalcançável.</p>
        <a href="contato.php" class="btn-contato">Fale Conosco e Faça Parte</a>
    </section>

</main>

<?php
// Inclui o Rodapé Global
require_once __DIR__ . '/footer.php';
?>