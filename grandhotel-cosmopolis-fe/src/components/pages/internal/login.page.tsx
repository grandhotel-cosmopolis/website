import { useState, MouseEvent, ChangeEvent } from "react";
import {
  eventApi,
  loginClient,
  retrieveCsrfToken,
  userClient,
} from "../../../infrastructure/api";
import {
  Button,
  Card,
  CardContent,
  FormControl,
  IconButton,
  InputAdornment,
  InputLabel,
  OutlinedInput,
  Stack,
  TextField,
  Typography,
  useTheme,
} from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import VisibilityIcon from "@mui/icons-material/Visibility";
import VisibilityOffIcon from "@mui/icons-material/VisibilityOff";
import { useNavigate } from "react-router-dom";

export const Login = () => {
  const [mail, setMail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [hasLoginError, setHasLoginError] = useState(false);
  const navigate = useNavigate();
  const theme = useTheme();

  const handleLogin = () => {
    retrieveCsrfToken().then(() =>
      loginClient
        .login(mail, password)
        .then(() => navigate("/internal/dashboard"))
        .catch(() => setHasLoginError(true))
    );
  };

  const getUser = () => {
    userClient.getUser().then((r) => console.log(r.data));
  };

  return (
    <ElementWrapper>
      <Card sx={{ width: "300px" }}>
        <CardContent>
          <Stack>
            <TextField
              sx={{ mt: 2 }}
              label="Email"
              onChange={(e) => setMail(e.target.value)}
            />
            <FormControl sx={{ mt: 2 }}>
              <InputLabel>Password</InputLabel>
              <OutlinedInput
                onChange={(e: ChangeEvent<HTMLTextAreaElement>) =>
                  setPassword(e.target.value)
                }
                type={showPassword ? "text" : "password"}
                endAdornment={
                  <InputAdornment position="end">
                    <IconButton
                      onClick={() => setShowPassword((curr) => !curr)}
                      onMouseDown={(e: MouseEvent<HTMLButtonElement>) =>
                        e.preventDefault()
                      }
                      edge="end"
                    >
                      {showPassword ? (
                        <VisibilityOffIcon />
                      ) : (
                        <VisibilityIcon />
                      )}
                    </IconButton>
                  </InputAdornment>
                }
                label="Password"
              />
            </FormControl>
            {hasLoginError && (
              <Typography color={theme.palette.error.main} variant="overline">
                Error logging in try again
              </Typography>
            )}
            <Button onClick={handleLogin}>Login</Button>
            <Button onClick={getUser}>GetUser</Button>
            <Button onClick={() => eventApi.getSingleEvents()}>
              test date
            </Button>
          </Stack>
        </CardContent>
      </Card>
    </ElementWrapper>
  );
};
