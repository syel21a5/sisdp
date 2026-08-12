# Tutorial de Implantação - SISDP (IP e APFD)

Este guia contém todos os passos necessários para replicar o ambiente de produção em um novo domínio ou servidor (ex: aaPanel), evitando erros de execução e configuração de IA.

## 1. Requisitos do Servidor
- **PHP**: Versão 8.2 ou superior.
- **Banco de Dados**: MySQL 8.0 ou AlmaLinux/Ubuntu equivalente.
- **Python**: Versão 3.10 ou superior (já vem no Linux Ubuntu 22.04+).

## 2. Passo a Passo da Instalação

### A. Preparação com Git
1. No terminal do servidor, entre na pasta `wwwroot`.
2. Clone o repositório: `git clone https://github.com/usuario/sisdp.git`.
3. Configure as permissões de pasta:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www:www .
   ```

### B. Configuração do Laravel
1. Instale as dependências: `composer install`.
2. Crie o arquivo `.env` (use o modelo já configurado).
3. **IMPORTANTE**: Certifique-se de que a `GEMINI_API_KEY` esteja presente no `.env` para a leitura de BOEs.
4. Gere a APP_KEY: `php artisan key:generate`.

### C. Configuração no aaPanel (Interface Gráfica)
1. No menu **Website**, aponte o "Running directory" para a pasta `/public`.
2. No menu **URL Rewrite**, selecione o modelo **laravel**.
3. No menu **SSL**, ative o Certificado (Let's Encrypt).

## 3. Configuração da IA e Scripts Python (CRÍTICO)

O sistema utiliza scripts Python para extrair dados dos PDFs dos BOEs. Para que funcione:

1. **Liberar Função PHP**:
   - Vá em **App Store** -> **PHP 8.2** -> **Setting**.
   - Aba **Disabled functions**: Remova a função `shell_exec` da lista.
   - Aba **Service**: Clique em **Restart**.

2. **Instalar Dependências no Linux**:
   Rode os comandos abaixo no terminal para instalar o instalador de pacotes e as bibliotecas da IA:
   ```bash
   # Instalar o PIP (Ubuntu/Debian)
   apt update && apt install python3-pip -y
   
   # Instalar bibliotecas de IA e PDF
   python3 -m pip install google-genai PyMuPDF
   ```

## 4. Banco de Dados
1. Crie um banco de dados vazio no aaPanel.
2. Importe o arquivo `database.sql` que está na raiz do projeto.
3. Atualize as credenciais no arquivo `.env`.

---

### Notas de Versão
- O código já inclui a correção de namespace (`\shell_exec`) para rodar em Linux.
- O sistema está otimizado para o fuso horário `America/Recife`.
