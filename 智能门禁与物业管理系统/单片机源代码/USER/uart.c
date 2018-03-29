

/******************** (C) COPYRIGHT  ��۵���Ƕ��ʽ���������� ***************************

 * �ļ���  ��uart3.c
 * ����    ������3ͨ�����ú�����     
 * ʵ��ƽ̨����۵���STM8������
 * ��汾  ��V2.0.0
 * ����    ��ling_guansheng  QQ��779814207
 * ����    ��
 *�޸�ʱ�� ��2011-12-20


****************************************************************************************/

#include "uart.h"
#include "stm8s.h"

/* ********************************************
UART3  configured as follow:
  - BaudRate = 115200 baud  
  - Word Length = 8 Bits
  - One Stop Bit
  - No parity
  - Receive and transmit enabled
 -  Receive interrupt
*********************************************/
void Uart_Init(void)
{
    UART1_DeInit();
    UART1_Init((u32)115200, UART1_WORDLENGTH_8D, UART1_STOPBITS_1, \
    UART1_PARITY_NO , UART1_SYNCMODE_CLOCK_DISABLE , UART1_MODE_TXRX_ENABLE);
    UART1_ITConfig(UART1_IT_RXNE_OR,ENABLE  );
    UART1_Cmd(ENABLE );
    
    UART3_DeInit();
    UART3_Init((u32)115200, UART3_WORDLENGTH_8D, UART3_STOPBITS_1, \
    UART3_PARITY_NO , UART3_MODE_TXRX_ENABLE);
    UART3_ITConfig(UART3_IT_RXNE_OR,ENABLE  );
    UART3_Cmd(ENABLE );
  
}

void UART1_SendByte(u8 data)
{
    UART1_SendData8((unsigned char)data);
  /* Loop until the end of transmission */
  while (UART1_GetFlagStatus(UART1_FLAG_TXE) == RESET);
}

void UART1_SendString(u8* Data,u16 len)
{
  u16 i=0;
  for(;i<len;i++)
    UART1_SendByte(Data[i]);
  
}
void UART3_SendByte(u8 data)
{
    UART3_SendData8((unsigned char)data);
  /* Loop until the end of transmission */
  while (UART3_GetFlagStatus(UART3_FLAG_TXE) == RESET);
    UART1_SendData8((unsigned char)data);
  /* Loop until the end of transmission */
  while (UART1_GetFlagStatus(UART1_FLAG_TXE) == RESET);
}

void UART3_SendString(u8* Data,u16 len)
{
  u16 i=0;
  for(;i<len;i++)
    UART3_SendByte(Data[i]);
  //i=0;
  //for(;i<len;i++)
  //  UART1_SendByte(Data[i]);
  
}

u8 UART3_ReceiveByte(void)
{
     u8 USART3_RX_BUF; 
     while (UART3_GetFlagStatus(UART3_FLAG_RXNE) == RESET);
     USART3_RX_BUF=UART3_ReceiveData8();
     return  USART3_RX_BUF;
    
}


/******************* (C) COPYRIGHT ��۵���Ƕ��ʽ���������� *****END OF FILE****/