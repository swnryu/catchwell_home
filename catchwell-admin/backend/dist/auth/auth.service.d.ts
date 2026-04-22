import { JwtService } from '@nestjs/jwt';
import { PrismaService } from '../prisma/prisma.service';
export declare class AuthService {
    private prisma;
    private jwt;
    constructor(prisma: PrismaService, jwt: JwtService);
    login(userid: string, password: string): Promise<{
        access_token: string;
        user: {
            userid: string;
            name: string;
            permission: number | null;
        };
    }>;
    private verifyPassword;
}
