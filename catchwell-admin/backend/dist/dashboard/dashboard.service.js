"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.DashboardService = void 0;
const common_1 = require("@nestjs/common");
const prisma_service_1 = require("../prisma/prisma.service");
let DashboardService = class DashboardService {
    prisma;
    constructor(prisma) {
        this.prisma = prisma;
    }
    async getSummary() {
        const todayStr = new Date().toISOString().slice(0, 10);
        const [asTotal, asPending, cancellationToday, shipmentToday, eventTotal] = await Promise.all([
            this.prisma.as_parcel_service.count(),
            this.prisma.as_parcel_service.count({ where: { process_state: { lte: 2 } } }),
            this.prisma.cancellation_order.count({
                where: { date: new Date(todayStr), status: 0 },
            }),
            this.prisma.shipping_date_new.count({
                where: { date: todayStr },
            }),
            this.prisma.cs_online_event.count(),
        ]);
        return {
            as: { total: asTotal, pending: asPending },
            cancellation: { today: cancellationToday },
            shipment: { today: shipmentToday },
            event: { total: eventTotal },
        };
    }
};
exports.DashboardService = DashboardService;
exports.DashboardService = DashboardService = __decorate([
    (0, common_1.Injectable)(),
    __metadata("design:paramtypes", [prisma_service_1.PrismaService])
], DashboardService);
//# sourceMappingURL=dashboard.service.js.map