
/******************** (C) COPYRIGHT  风驰电子嵌入式开发工作室 ********************
 * 文件名  ：main.c
 * 描述    ：串口3通信     
 * 实验平台：风驰电子STM8开发板
 * 库版本  ：V2.0.0
 * 作者    ：ling_guansheng  QQ：779814207
 * 博客    ：
 *修改时间 ：2011-12-20
**********************************************************************************/

/* Includes ------------------------------------------------------------------*/
/* Includes ------------------------------------------------------------------*/
#include "stm8s.h"
#include "stm8s_clk.h"
#include "intrinsics.h"
#include "stm8s_uart3.h"
#include "uart.h"
#include "stm8s_gpio.h"
#include "stdio.h"
#include "string.h"

#define ON  0
#define OFF 1
#define JDQ(ON_OFF)  if(ON_OFF==ON)GPIO_WriteHigh(GPIOA, GPIO_PIN_6);\
                      else GPIO_WriteLow(GPIOA, GPIO_PIN_6)
#define SIM(ON_OFF)  if(ON_OFF==ON)GPIO_WriteLow(GPIOD, GPIO_PIN_3);\
                      else GPIO_WriteHigh(GPIOD, GPIO_PIN_3)

u32 sleep;
u32 reboot = 0;
u32 pd = 0;
u32 i = 0;
u8* dpjid = "d:1";//单片机编号
u16 idlen = sizeof("d:");//少写一位数

extern u8 RxBuffer[RxBufferSize];
extern u8 UART_RX_NUM;
void Delay(u32 nCount);
void delbuf();
void start(void);
void cipshut(void);
void cstt(void);
void ciicr(void);
void cifsr(void);
void cipstart(void);
void cipsend(u8* data,u16 datalen,u32 n);
void checkser(void);

int main1(void)//测试
{
  //u8 len ;
  /* Infinite loop */
  /*设置内部时钟16M为主时钟*/ 
   CLK_HSIPrescalerConfig(CLK_PRESCALER_HSIDIV1);
  /*!<Set High speed internal clock  */
  Uart_Init();
  __enable_interrupt();
  GPIO_Init(GPIOA,(GPIO_PIN_6),\
             GPIO_MODE_OUT_PP_LOW_FAST);//定义JDQ的管脚的模式，高速推挽低電平輸出，高电平开，低电平关
  GPIO_Init(GPIOD,(GPIO_PIN_3),\
             GPIO_MODE_OUT_PP_HIGH_FAST);//定义sim的管脚的模式,高速推挽高電平輸出 低电平开，高电平关
  

   Delay(65535*10*4);
  GPIO_WriteLow(GPIOD, GPIO_PIN_3);;
  Delay(65535*10*4);
  GPIO_WriteHigh(GPIOD, GPIO_PIN_3);
  while(1){
    Delay(65535*10*4);
    JDQ(ON);
    Delay(65535*10*4);
    JQD(OFF);
    
  }

}

int main(void)
{
  //u8 len ;
  /* Infinite loop */
  /*设置内部时钟16M为主时钟*/ 
  CLK_HSIPrescalerConfig(CLK_PRESCALER_HSIDIV1);
  /*!<Set High speed internal clock  */
  Uart_Init();
  __enable_interrupt();
  GPIO_Init(GPIOA,(GPIO_PIN_6),\
             GPIO_MODE_OUT_PP_LOW_FAST);//定义JDQ的管脚的模式，高速推挽低電平輸出，高电平开，低电平关
  GPIO_Init(GPIOD,(GPIO_PIN_3),\
             GPIO_MODE_OUT_PP_HIGH_FAST);//定义sim的管脚的模式,高速推挽高電平輸出 低电平开，高电平关
  

  while(1)
  {
    start();//开机 检测开机 检测运营商接入
    if(!reboot) cstt();//设置apn
    if(!reboot) ciicr();//激活移动场景
    if(!reboot) cifsr();//获取IP
    if(!reboot) cipstart();//连接服务器
    if(!reboot) cipsend(dpjid,idlen,3);//发送数据
    JDQ(OFF);
    while(!reboot)
    {
      //if(!reboot) 
      checkser();//接收数据 检查心跳
      cipsend("tt",sizeof("t"),2);
      
    }
    JDQ(ON);
    UART3_SendString("AT+CPOWD=1\r\n", sizeof("AT+CPOWD=1\r"));//900a 关机
    Delay(65535*10*5);
  }
  
  
}

void Delay(u32 nCount)
{
  /* Decrement nCount value */
  while (nCount != 0)
  {
    nCount--;
  }
}

void delbuf()
{
  while(--UART_RX_NUM)
  {
    RxBuffer[UART_RX_NUM]='.';
  }
  UART_RX_NUM = 0;
}

void start(void)
{
  reboot = 1;
  SIM(ON);
  Delay(65535*10*2);
  SIM(OFF);

  delbuf();
  i = 20;
  while(i != 0)
  {
    i--;
    UART3_SendString("ATV1\r\n", sizeof("ATV1\r"));//显示格式
    Delay(65535*10*2);
    if(UART_RX_NUM > 5)
    {
      break;
    }
    
  }

  UART3_SendString("ATE1\r\n", sizeof("ATE1\r"));//握手并设置回显
  Delay(65535*10);
  UART3_SendString("ATS10=254\r\n", sizeof("ATS10=254\r"));//挂机延时
  Delay(65535*10);
  delbuf();
  i = 20;
  while(i != 0)
  {
    i--;
    UART3_SendString("AT+COPS?\r\n", sizeof("AT+COPS?\r"));//检测搜索网络是否完毕
    Delay(65535*10*2);
    if((UART_RX_NUM > 12) && (strstr(RxBuffer,"CHINA") != NULL))
    {
      reboot = 0;
      break;
    }
    if(UART_RX_NUM > 12)
    {
      delbuf();
    }
  }
  
}



void cstt(void)
{
  delbuf();
  i = 30;
  reboot = 1;
  while(i != 0)
  {
    i--;
    UART3_SendString("AT+CSTT\r\n", sizeof("AT+CSTT\r"));//设置APN  CMWAP
    Delay(65535*10);
    while(1)
    {
      if((strstr(RxBuffer,"OK") != NULL))
      {
        pd=1;
        break;
      }
      if((strstr(RxBuffer,"ERROR") != NULL))
      {
        delbuf();
        break;
      }
      
    }
    if(pd)
    {
      pd=0;
      reboot = 0;
      break;
    }
    
  }
  
}


void ciicr(void)
{
  i = 5;
  reboot = 1;
  delbuf();
  while(i != 0)
  {
    i--;
    UART3_SendString("AT+CIICR\r\n", sizeof("AT+CIICR\r"));//激活移动场景
    Delay(65535);
    sleep = 65535*3;
    while(sleep != 0)
    {
      sleep--;
      if((strstr(RxBuffer,"OK") != NULL))
      {
        pd=1;
        break;
      }

      if((strstr(RxBuffer,"ERROR") != NULL) || (sleep == 0))
      {
        
        delbuf();
        break;
      }
      
    }
    if(pd)
    {
      pd=0;
      reboot = 0;
      break;
    }

    
  }

}


void cifsr(void)
{
  delbuf();
  while(1)
  {
    UART3_SendString("AT+CIFSR\r\n", sizeof("AT+CIFSR\r"));//获取本地IP 之后才能连接
    Delay(65535);

    if(UART_RX_NUM > 10)
    {
      break;
    }
    if((strstr(RxBuffer,"ERROR") != NULL))
    {
      reboot = 1;
      break;
    }
  }
}

void cipstart(void)
{
  //UART3_SendString("AT+CIPCLOSE\r\n", sizeof("AT+CIPCLOSE\r"));
  //Delay(65535*10*2);
  pd = 0;
  i = 3;//运行3次
  reboot = 1;
  delbuf();
  while(i != 0)
  {
    i--;
    UART3_SendString("AT+CIPSTART=\"TCP\",\"120.76.233.4\",8888\r\n", sizeof("AT+CIPSTART=\"TCP\",\"120.76.233.4\",8888\r"));//此处修改你建立服务器的IP，服务器端口号33582
    Delay(65535);
    sleep = 65535*3;
    while(sleep != 0)
    {
      sleep--;
      if((strstr(RxBuffer,"CONNECT OK") != NULL))
      {
        UART1_SendString("\r\n1\r\n", sizeof("\r\n1\r"));
        
        pd=1;
        break;
      }
      if(sleep == 0)
      {
        delbuf();
        break;
      }
    }
    if(pd)
    {
      pd=0;
      reboot = 0;
      break;
    }
    
  }
}


void cipsend(u8* data,u16 datalen,u32 n)
{
  delbuf();
  reboot = 1;
  while(n != 0){
    n--;
    UART3_SendString("AT+CIPSEND\r\n", sizeof("AT+CIPSEND\r"));
    Delay(65535);
    UART3_SendString(data, datalen);//向服务器发送数据
    Delay(65535);
    UART3_SendByte(0x1a);//以0x1a结束
    sleep = 65535*3;
    while(sleep != 0)
    {
      sleep--;
      if((strstr(RxBuffer,"SEND OK") != NULL))
      {
        pd=1;
        break;
      }
      if((strstr(RxBuffer,"CLOSED") != NULL))
      {
        delbuf();
        break;
      }
      if((strstr(RxBuffer,"ERROR") != NULL))
      {
        delbuf();
        break;
      }
      if(sleep == 0)
      {
        delbuf();
        break;
      }
      
    }
    if(pd)
    {
      pd=0;
      reboot = 0;
      break;
    }

  }
}


void checkser(void)
{
  delbuf();
  sleep = 0;
  
  while (sleep < 250)
  {
    //if(UART_RX_NUM&0x80)
    if(UART_RX_NUM > 0)
    {
      //len=UART_RX_NUM&0x3f;/*得到此次接收到的数据长度*/
      //UART3_SendString("You sent the messages is:",sizeof("You sent the messages is"));
      //UART3_SendString(RxBuffer,len);
      //UART3_SendByte('\n');
      //UART_RX_NUM=0;

      if(strstr(RxBuffer,"t") != NULL)
      {
        //__disable_interrupt();//关闭中断
        UART3_SendString("AT+CIPSEND\r\n", sizeof("AT+CIPSEND\r"));
        Delay(65535);
        UART3_SendByte('t');//向服务器发送数据
        Delay(65535);
        UART3_SendByte(0x1a);//以0x1a结束

        //__enable_interrupt();//开启中断
      }
      else if(strstr(RxBuffer,"o") != NULL)
      {
        __disable_interrupt();//关闭中断
        JDQ(ON);
        Delay(65535*10*2);
        JDQ(OFF);
        UART3_SendString("AT+CIPSEND\r\n", sizeof("AT+CIPSEND\r"));
        Delay(65535);
        UART3_SendByte('y');//向服务器发送数据 开锁成功
        Delay(65535);
        UART3_SendByte(0x1a);//以0x1a结束
        __enable_interrupt();//开启中断

        
      }
      else if(strstr(RxBuffer,"C") != NULL)
      {
        delbuf();
        break;        
      }
      delbuf();
      sleep = 0;
    }
    
    sleep++;
    Delay(65535);
  }

}
#ifdef USE_FULL_ASSERT

/**
  * @brief  Reports the name of the source file and the source line number
  *   where the assert_param error has occurred.
  * @param file: pointer to the source file name
  * @param line: assert_param error line source number
  * @retval : None
  */
void assert_failed(u8* file, u32 line)
{ 
  /* User can add his own implementation to report the file name and line number,
     ex: printf("Wrong parameters value: file %s on line %d\r\n", file, line) */

  /* Infinite loop */
  while (1)
  {
    
  }
}
#endif

/******************* (C) COPYRIGHT 风驰电子嵌入式开发工作室 *****END OF FILE****/
