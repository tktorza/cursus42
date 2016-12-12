export class DateHelper {

    static ddMMyyyy(date: Date): string {
        var mm = (date.getMonth() + 1).toString(); // getMonth() is zero-based
        var dd = date.getDate().toString();

        return [dd.length===2 ? '' : '0', dd,'-', mm.length===2 ? '' : '0', mm,'-',date.getFullYear()].join('');
    }

    static HHmm(date: Date): string{
        return date.getHours()+":"+(date.getMinutes().toString().length === 2 ? '' : '0')+date.getMinutes();
    }

    static yyyyMMddHHmm(date: Date): string {
        return DateHelper.ddMMyyyy(date)+" "+DateHelper.HHmm(date);
    }
}