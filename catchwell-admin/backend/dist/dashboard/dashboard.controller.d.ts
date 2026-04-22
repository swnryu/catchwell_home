import { DashboardService } from './dashboard.service';
export declare class DashboardController {
    private dashboardService;
    constructor(dashboardService: DashboardService);
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
