
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
</head>
<body>
    <header>
        <nav>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.html">Início</a></li>
                <li><a href="">Sobre</a></li>
                <li><a href="">Requisitos</a></li>
                <li><a href="">Unidades</a></li>
                <li><a href="">Contato</a></li>
            </ul>
            <button class="mobile-menu" id="mobileMenu">☰</button>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1 class="section-title">Agende Sua Doação</h1>
            <div class="card">
                <div id="alertBox" class="alert"></div>
                <form id="agendamentoForm">
                    <div class="grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo :</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail :</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone :</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                        </div>
                        <div class="form-group">
                            <label for="cpf">CPF :</label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                        </div>
                    </div>
                    
                    <div class="grid">
                        <div class="form-group">
                            <label for="unidade">Unidade :</label>
                            <select id="unidade" name="unidade" required>
                                <option value="">Selecione uma unidade</option>
                                <option value="centro">Centro - Av. Principal, 123</option>
                                <option value="norte">Zona Norte - Rua A, 456</option>
                                <option value="sul">Zona Sul - Av. B, 789</option>
                                <option value="leste">Zona Leste - Rua C, 321</option>
                                <option value="oeste">Zona Oeste - Av. D, 654</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="data">Data *</label>
                            <input type="date" id="data" name="data" required>
                        </div>
                        <div class="form-group">
                            <label for="hora">Horário :</label>
                            <select id="hora" name="hora" required>
                                <option value="">Selecione um horário</option>
                                <option value="08:00">08:00</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Confirmar Agendamento</button>
                </form>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Gota Vermelha</h3>
                <p>Salvando vidas através da solidariedade e do compromisso com a saúde da nossa comunidade.</p>
            </div>
            <div class="footer-section">
                <h3>Informações</h3>
                <ul>
                    <li>📍 Av. Principal, 123 - Centro</li>
                    <li>📞 (11) 3333-4000</li>
                    <li>📧 contato@gotavermelha.org.br</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Gota Vermelha. Todos os direitos reservados.</p>
            <p>Site desenvolvido com ❤️</p>
        </div>
    </footer>
