#include "tim1.h"
#include "stm8s_tim1.h"
static  u32 TimingDelay; 
u32 checkon = 0;
u32 online = 1;
void Tim1_Init(void)
{
  TIM1_TimeBaseInit(16,TIM1_COUNTERMODE_UP,65535*10*30,0);
  TIM1_ARRPreloadConfig(ENABLE);
  TIM1_ITConfig(TIM1_IT_UPDATE , ENABLE);
  TIM1_Cmd(ENABLE);
}



void TimingDelay_Decrement(void)
{
  online = 1;

}

void delay_ms( u32 nTime)
{
  TimingDelay = nTime;

  while(TimingDelay != 0);
}