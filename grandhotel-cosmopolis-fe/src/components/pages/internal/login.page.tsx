import { Button, Card, Input } from "@chakra-ui/react";
import { useState } from "react";
import { loginClient, retrieveCsrfToken } from "../../../infrastructure/api";

export const Login = () => {
  const [mail, setMail] = useState("");
  const [password, setPassword] = useState("");

  const retrieveCsrf = () => {
    retrieveCsrfToken();
  };

  const handleLogin = () => {
    loginClient.login(mail, password);
  };

  return (
    <Card maxW={300} border="1px" p={10}>
      <Input placeholder="email" onChange={(e) => setMail(e.target.value)} />
      <Input
        mt={4}
        placeholder="password"
        type="password"
        onChange={(e) => setPassword(e.target.value)}
      />
      <Button mt={4} onClick={retrieveCsrf}>
        Retrieve csrf
      </Button>
      <Button mt={4} onClick={handleLogin}>
        Login
      </Button>
    </Card>
  );
};
