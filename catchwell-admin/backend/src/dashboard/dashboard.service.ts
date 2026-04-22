import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class DashboardService {
  constructor(private prisma: PrismaService) {}

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
}
