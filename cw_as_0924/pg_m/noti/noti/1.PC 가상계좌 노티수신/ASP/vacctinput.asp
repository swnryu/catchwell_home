<% 

'*******************************************************************************
' FILE NAME : vacctinput.asp
' DATE : 2006.09
' 이니시스 가상계좌 입금내역 처리demon으로 넘어오는 파라메터를 control 하는 부분 입니다.
'*******************************************************************************

'**********************************************************************************
'이니시스가 전달하는 가상계좌이체의 결과를 수신하여 DB 처리 하는 부분 입니다.	
'필요한 파라메터에 대한 DB 작업을 수행하십시오.
' [수신정보] 자세한 내용은 메뉴얼 참조
'**********************************************************************************	


TEMP_IP = Request.ServerVariables("REMOTE_ADDR")
PG_IP	= Left(TEMP_IP,10)

IF PG_IP = "203.238.37" OR PG_IP = "39.115.212" OR PG_IP = "183.109.71"  THEN  'PG에서 보냈는지 IP로 체크 

	
	NO_TID = Request("NO_TID")		'거래번호
	NO_OID = Request("NO_OID") 		'상점 주문번호
	ID_MERCHANT = Request("ID_MERCHANT")	'상점 아이디
	CD_BANK = Request("CD_BANK")		'거래 발생 기관 코드
	CD_DEAL = Request("CD_DEAL")		'취급 기관 코드	
	DT_TRANS = Request("DT_TRANS")		'거래 일자 
	TM_TRANS = Request("TM_TRANS")		'거래 시간
	NO_MSGSEQ = Request("NO_MSGSEQ")	'전문 일련 번호
	CD_JOINORG = Request("CD_JOINORG")	'제휴 기관 코드
	
	DT_TRANSBASE = Request("DT_TRANSBASE")	'거래 기준 일자
	NO_TRANSEQ = Request("NO_TRANSEQ")	'거래 일련 번호
	TYPE_MSG = Request("TYPE_MSG")		'거래 구분 코드 
	CL_CLOSE = Request("CL_CLOSE")		'마감 구분코드
	CL_KOR = Request("CL_KOR")		'한글 구분 코드
	NO_MSGMANAGE = Request("NO_MSGMANAGE")	'전문 관리 번호
	NO_VACCT = Request("NO_VACCT")		'가상계좌번호
	AMT_INPUT = Request("AMT_INPUT")	'입금금액
	AMT_CHECK = Request("AMT_CHECK")	'미결제 타점권 금액
	NM_INPUTBANK = Request("NM_INPUTBANK")	'입금 금융기관명
	NM_INPUT = Request("NM_INPUT")		'입금 의뢰인
	DT_INPUTSTD = Request("DT_INPUTSTD")	'입금 기준 일자
	DT_CALCULSTD = Request("DT_CALCULSTD")	'정산 기준 일자
	FLG_CLOSE = Request("FLG_CLOSE")	'마감 전화 

' 가상계좌채번시 현금영수증 자동발급신청시에만 전달
  DT_CSHR      = Request("DT_CSHR")      '현금영수증 발급일자
  TM_CSHR      = Request("TM_CSHR")      '현금영수증 발급시간
  NO_CSHR_APPL = Request("NO_CSHR_APPL") '현금영수증 발급번호
  NO_CSHR_TID  = Request("NO_CSHR_TID")  '현금영수증 발급TID
	
	Set objFSO = CreateObject("Scripting.FileSystemObject")

'**********************************************************************************
'   이부분에 로그파일 경로를 수정해주세요.	

    Set f = objFSO.CreateTextFile("c:\inipay41\log\result.log",True)

'**********************************************************************************	

    f.WriteLine("************************************************")
    f.WriteLine("ID_MERCHANT : " + ID_MERCHANT)
    f.WriteLine("NO_TID : " + NO_TID)
    f.WriteLine("NO_OID : " + NO_OID)
    f.WriteLine("NO_VACCT : " + NO_VACCT)
    f.WriteLine("AMT_INPUT : " + AMT_INPUT)
    f.WriteLine("NM_INPUTBANK : " + NM_INPUTBANK)
    f.WriteLine("NM_INPUT : " + NM_INPUT)
    f.WriteLine("************************************************")
    f.WriteLine("")

    
'	f.WriteLine("전체 결과값")
'	f.WriteLine(msg_id)
'	f.WriteLine(NO_TID)
'	f.WriteLine(NO_OID)
'	f.WriteLine(ID_MERCHANT)
'	f.WriteLine(CD_BANK)
'	f.WriteLine(DT_TRANS)
'	f.WriteLine(TM_TRANS)
'	f.WriteLine(NO_MSGSEQ)
'	f.WriteLine(TYPE_MSG)
'	f.WriteLine(CL_CLOSE)
'	f.WriteLine(CL_KOR)
'	f.WriteLine(NO_MSGMANAGE)
'	f.WriteLine(NO_VACCT)
'	f.WriteLine(AMT_INPUT)
'	f.WriteLine(AMT_CHECK)
'	f.WriteLine(NM_INPUTBANK)
'	f.WriteLine(NM_INPUT)
'	f.WriteLine(DT_INPUTSTD)
'	f.WriteLine(DT_CALCULSTD)
'	f.WriteLine(FLG_CLOSE)
	f.Close
	

	
'************************************************************************************

	'위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로
	'리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
	'(주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
	'기타 다른 형태의 PRINT(response.write)는 하지 않으시기 바랍니다
	
'	IF (데이터베이스 등록 성공 유무 조건변수 = true) THEN

		response.write "OK" 			  ' 절대로 지우지마세요 
	
'*************************************************************************************	

	END IF

%>
