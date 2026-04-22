
<%

'*******************************************************************************
' FILE NAME : mx_rnoti.asp
' FILE DESCRIPTION :
' 이니시스 smart phone 결제 결과 수신 페이지 샘플
 '기술문의 : ts@inicis.com
' HISTORY 
 '2010. 02. 25 최초작성 
 '2010  06. 23 WEB 방식의 가상계좌 사용시 가상계좌 채번 결과 무시 처리 추가(APP 방식은 해당 없음!!)
 'WEB 방식일 경우 이미 P_NEXT_URL 에서 채번 결과를 전달 하였으므로, 
 '이니시스에서 전달하는 가상계좌 채번 결과 내용을 무시 하시기 바랍니다.
'*******************************************************************************

'이 페이지는 수정하지 마십시요. 수정시 html태그나 자바스크립트가 들어가는 경우 동작을 보장할 수 없습니다
'그리고 정상적으로 data를 처리한 경우에도 이니시스에서 응답을 받지 못한 경우는 결제결과가 중복해서 나갈 수
'있으므로 관련한 처리도 고려되어야 합니다. 
Response.CacheControl = "no-cache"
Response.AddHeader "Pragma", "no-cache"
Response.Expires = -1
%>

<%


'**********************************************************************************
' 처리 흐름
'1) 결과 결과 수신 => 2) 상점 DB 처리 => 3) DB 처리 성공시 "OK 응답" 실패시 "FAIL" 응답
'**********************************************************************************

PGIP = Request.ServerVariables("REMOTE_ADDR")

IF PGIP = "118.129.210.25" OR  PGIP = "203.238.37.15" OR PGIP = "183.109.71.153" THEN  'PG에서 보냈는지 IP로 체크 


	'이니시스 NOTI 서버에서 받은 Value
	Dim P_TID				' 거래번호
	Dim P_MID				' 상점아이디
	Dim P_AUTH_DT			' 승인일자
	Dim P_STATUS			' 거래상태 (00:성공, 01:실패)
	Dim P_TYPE				' 지불수단
	Dim P_OID				' 상점주문번호
	Dim P_FN_CD1			' 금융사코드1
	Dim P_FN_CD2			' 금융사코드2
	Dim P_FN_NM				' 금융사명 (은행명, 카드사명, 이통사명)
	Dim P_AMT				' 거래금액
	Dim P_UNAME				' 결제고객성명
	Dim P_RMESG1			' 결과코드
	Dim P_RMESG2			' 결과메시지
	Dim P_NOTI				' 노티메시지(상점에서 올린 메시지)
	Dim P_AUTH_NO			' 승인번호

	Dim resp, noti(15), resp_time



	'noti server에서 받은 value
	resp_time		= Now()
	P_TID			= trim(request("P_TID"))
	P_MID			= trim(request("P_MID"))
	P_AUTH_DT		= trim(request("P_AUTH_DT"))
	P_STATUS		= trim(request("P_STATUS"))
	P_TYPE			= trim(request("P_TYPE"))
	P_OID			= trim(request("P_OID"))
	P_FN_CD1		= trim(request("P_FN_CD1"))
	P_FN_CD2		= trim(request("P_FN_CD2"))
	P_FN_NM			= trim(request("P_FN_NM"))
	P_AMT			= trim(request("P_AMT"))
	P_UNAME			= trim(request("P_UNAME"))
	P_RMESG1		= trim(request("P_RMESG1"))
	P_RMESG2		= trim(request("P_RMESG2"))
	P_NOTI			= trim(request("P_NOTI"))
	P_AUTH_NO		= trim(request("P_AUTH_NO"))
	 
	   
	'WEB 방식의 경우 가상계좌 채번 결과 무시 처리
	'(APP 방식의 경우 해당 내용을 삭제 또는 주석 처리 하시기 바랍니다.)
	IF P_TYPE = "VBANK" THEN		'결제수단이 가상계좌이며	 
		IF P_STATUS <> "02"	THEN	'입금통보 "02" 가 아니면(가상계좌 채번 : 00 또는 01 경우)		
		Response.Write("OK")
		Response.End		
		END IF
	END IF
	   
	   
	noti(0) = resp_time
	noti(1) = P_TID
	noti(2) = P_MID
	noti(3) = P_AUTH_DT
	noti(4) = P_STATUS
	noti(5) = P_TYPE
	noti(6) = P_OID
	noti(7) = P_FN_CD1
	noti(8) = P_FN_CD2
	noti(9) = P_FN_NM
	noti(10) = P_AMT
	noti(11) = P_UNAME
	noti(12) = P_RMESG1
	noti(13) = P_RMESG2
	noti(14) = P_NOTI
	noti(15) = P_AUTH_NO

	'***********************************************************************************
	 ' 위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로 실패시는 "FAIL" 을
	 ' 리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
	 ' (주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
	'  기타 다른 형태의 Response.Write는 하지 않으시기 바랍니다
	'***********************************************************************************

	'	IF (데이터베이스 등록 성공 유무 조건변수 = true) THEN

	     		Response.Write("OK") '절대로 지우지 마세요
	'	ELSE
	'   		 Response.Write("FAIL")
	'	ENDIF

	'**********************************************************************************
	'이부분에 로그파일 경로를 수정해주세요.	
	'로그를 남기셔야 오류 발생시 오류 추적이 가능 합니다.
	logdate		=	year(now) & right("0" & month(now),2) & right("0" & day(now),2)	
	logfilename	=	"noti_input_"& f_tempDate & logdate & ".log"

	filepath = "c:\\"  & logfilename  '로그를 기록할 디렉터리
	'**********************************************************************************
	 writeLog filepath , noti

END IF

Function writeLog(file, noti)

    Dim fso, ofile, slog

    slog = ""
    slog = slog & "PageCall time:"	& noti(0) & Chr(10)
    slog = slog & "P_TID:"			& noti(1) & Chr(10)
    slog = slog & "P_MID:"			& noti(2) & Chr(10)
    slog = slog & "P_AUTH_DT:"		& noti(3) & Chr(10)
    slog = slog & "P_STATUS:"		& noti(4) & Chr(10)
    slog = slog & "P_TYPE:"			& noti(5) & Chr(10)
    slog = slog & "P_OID:"			& noti(6) & Chr(10)
    slog = slog & "P_FN_CD1:"		& noti(7) & Chr(10)
    slog = slog & "P_FN_CD2:"		& noti(8) & Chr(10)
    slog = slog & "P_FN_NM:"		& noti(9) & Chr(10)
    slog = slog & "P_AMT:"			& noti(10) & Chr(10)
    slog = slog & "P_UNAME:"		& noti(11) & Chr(10)
    slog = slog & "P_RMESG1:"		& noti(12) & Chr(10)
    slog = slog & "P_RMESG2:"		& noti(13) & Chr(10)
    slog = slog & "P_NOTI:"			& noti(14) & Chr(10)
    slog = slog & "P_AUTH_NO:"		& noti(15) & Chr(10)
        
    
    Set fso = Server.CreateObject("Scripting.FileSystemObject")
    if fso.fileExists(file) then    
        Set ofile = fso.OpenTextFile(file, 8, True)
    else
        Set ofile = fso.CreateTextFile(file, True)
    end if
    
    ofile.Writeline slog

    ofile.close
    Set ofile = Nothing
    Set fso = Nothing
End Function

%>
    


