package codigos.config;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;

public class admin {
// Atributos privados
    private String login;
    private int senha;

    // Método construtor
    public void Usuario(String login, int senha) {
        this.login = login;
        this.senha = senha;
    }

    // Getter e Setter do login
    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    // Getter e Setter da senha
    public int getSenha() {
        return senha;
    }

    public void setSenha(int senha) {
        this.senha = senha;
    }

    public void inserirNoBanco() {
        System.out.println("Inserindo usuário no banco de dados...");
        String url = "jdbc:mysql://localhost:3306/jupter_dados";
        String usuario = "root";
        String senhaBanco = "";

        String sql = "INSERT INTO usuario (login, senha) VALUES (?, ?)";

        try {
            Connection conexao = DriverManager.getConnection(url, usuario, senhaBanco);
            PreparedStatement stmt = conexao.prepareStatement(sql);

            stmt.setString(1, this.login);
            stmt.setInt(2, this.senha);

            stmt.executeUpdate();

            System.out.println("Usuário cadastrado com sucesso!");

            stmt.close();
            conexao.close();

        } catch (SQLException e) {
            System.out.println("Erro ao inserir no banco.");
            e.printStackTrace();
        }
    }
}