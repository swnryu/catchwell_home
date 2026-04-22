import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import * as bcrypt from 'bcrypt';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwt: JwtService,
  ) {}

  async login(userid: string, password: string) {
    const user = await this.prisma.admin_account.findUnique({
      where: { admin_userid: userid },
    });

    if (!user) throw new UnauthorizedException('아이디 또는 비밀번호가 틀렸습니다.');

    const isValid = await this.verifyPassword(password, user.admin_passwd, userid);
    if (!isValid) throw new UnauthorizedException('아이디 또는 비밀번호가 틀렸습니다.');

    const payload = {
      sub: user.admin_userid,
      name: user.admin_name,
      permission: user.permission,
    };

    return {
      access_token: this.jwt.sign(payload),
      user: {
        userid: user.admin_userid,
        name: user.admin_name,
        permission: user.permission,
      },
    };
  }

  private async verifyPassword(input: string, stored: string, userid: string): Promise<boolean> {
    if (!stored) return false;

    // bcrypt 해시인 경우
    if (stored.startsWith('$2y$') || stored.startsWith('$2b$')) {
      const normalized = stored.replace(/^\$2y\$/, '$2b$');
      return bcrypt.compare(input, normalized);
    }

    // 평문 비교 (구버전 계정)
    if (input === stored) return true;

    // 기본 비밀번호 규칙 (userid@catchwell.com)
    if (input === `${userid}@catchwell.com`) return true;

    return false;
  }
}
