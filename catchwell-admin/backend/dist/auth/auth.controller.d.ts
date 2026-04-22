import { AuthService } from './auth.service';
export declare class AuthController {
    private authService;
    constructor(authService: AuthService);
    login(body: {
        userid: string;
        password: string;
    }): Promise<{
        access_token: string;
        user: {
            userid: string;
            name: string;
            permission: number | null;
        };
    }>;
}
