import { PrismaService } from '../prisma/prisma.service';
export declare class DashboardService {
    private prisma;
    constructor(prisma: PrismaService);
    getSummary(): Promise<{
        as: {
            total: number;
            pending: number;
        };
        cancellation: {
            today: number;
        };
        shipment: {
            today: number;
        };
        event: {
            total: number;
        };
    }>;
}
