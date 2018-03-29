#ifndef _TYPEDEFINE_H_
#define _TYPEDEFINE_H_


// 定义芯片类型
#define STM8S207
//#define	STM8S103
 
//===============   I/O位定义  ============
//== INPUT ===
//---- PA -----
#define  PA_IN		PA_IDR
 _Bool   PA1_IN   @PA_IDR:1; 
 _Bool   PA2_IN   @PA_IDR:2; 
 _Bool   PA3_IN   @PA_IDR:3; 
 
//---- PB -----
#define  PB_IN		PB_IDR
 _Bool   PB0_IN   @PB_IDR:0; 
 _Bool   PB1_IN   @PB_IDR:1; 
 _Bool   PB2_IN   @PB_IDR:2; 
 _Bool   PB3_IN   @PB_IDR:3; 
 _Bool   PB4_IN   @PB_IDR:4;  
 _Bool   PB5_IN   @PB_IDR:5; 
 _Bool   PB6_IN   @PB_IDR:6; 
 _Bool   PB7_IN   @PB_IDR:7; 
 
//---- PC -----
#define  PC_IN		PC_IDR
 _Bool   PC0_IN   @PC_IDR:0; 
 _Bool   PC1_IN   @PC_IDR:1; 
 _Bool   PC2_IN   @PC_IDR:2; 
 _Bool   PC3_IN   @PC_IDR:3; 
 _Bool   PC4_IN   @PC_IDR:4;  
 _Bool   PC5_IN   @PC_IDR:5; 
 _Bool   PC6_IN   @PC_IDR:6; 
 _Bool   PC7_IN   @PC_IDR:7;  

//---- PD -----
#define  PD_IN		PD_IDR
 _Bool   PD0_IN   @PD_IDR:0; 
 _Bool   PD1_IN   @PD_IDR:1; 
 _Bool   PD2_IN   @PD_IDR:2; 
 _Bool   PD3_IN   @PD_IDR:3; 
 _Bool   PD4_IN   @PD_IDR:4;  
 _Bool   PD5_IN   @PD_IDR:5; 
 _Bool   PD6_IN   @PD_IDR:6; 
 _Bool   PD7_IN   @PD_IDR:7; 
 
//---- PE -----
#define  PE_IN		PE_IDR
 _Bool   PE5_IN  @PE_IDR:5; 

//---- PF -----
#define  PF_IN		PF_IDR
 _Bool   PF4_IN  @PF_IDR:4; 
 
//==  OUTPUT ===
//---- PA -----
#define  PA_OUT		 PA_ODR
 _Bool   PA1_OUT   @PA_ODR:1; 
 _Bool   PA2_OUT   @PA_ODR:2; 
 _Bool   PA3_OUT   @PA_ODR:3; 
 
//---- PB -----
#define  PB_OUT		 PB_ODR
 _Bool   PB0_OUT   @PB_ODR:0; 
 _Bool   PB1_OUT   @PB_ODR:1; 
 _Bool   PB2_OUT   @PB_ODR:2; 
 _Bool   PB3_OUT   @PB_ODR:3; 
 _Bool   PB4_OUT   @PB_ODR:4;  
 _Bool   PB5_OUT   @PB_ODR:5; 
 _Bool   PB6_OUT   @PB_ODR:6; 
 _Bool   PB7_OUT   @PB_ODR:7; 
 
//---- PC -----
#define  PC_OUT		 PC_ODR
 _Bool   PC1_OUT  @PC_ODR:1; 
 _Bool   PC2_OUT  @PC_ODR:2; 
 _Bool   PC3_OUT  @PC_ODR:3; 
 _Bool   PC4_OUT  @PC_ODR:4; 
 _Bool   PC5_OUT  @PC_ODR:5; 
 _Bool   PC6_OUT  @PC_ODR:6; 
 _Bool   PC7_OUT  @PC_ODR:7; 
 
//---- PD -----
#define  PD_OUT		 PD_ODR
 _Bool   PD0_OUT  @PD_ODR:0; 
 _Bool   PD1_OUT  @PD_ODR:1; 
 _Bool   PD2_OUT  @PD_ODR:2; 
 _Bool   PD3_OUT  @PD_ODR:3; 
 _Bool   PD4_OUT  @PD_ODR:4; 
 _Bool   PD5_OUT  @PD_ODR:5; 
 _Bool   PD6_OUT  @PD_ODR:6; 
 _Bool   PD7_OUT  @PD_ODR:7; 
 
//---- PE -----
#define  PE_OUT		 PE_ODR
 _Bool   PE5_OUT  @PE_ODR:5; 

//---- PF -----
#define  PF_OUT		 PF_ODR
 _Bool   PF4_OUT  @PF_ODR:4;  

//=====   I/O 方向寄存器定义
//---- PA ---------
#define  PA_M	 	PA_DDR
 _Bool   PA1_M  @PA_DDR:1;  
 _Bool   PA2_M  @PA_DDR:2;  
 _Bool   PA3_M  @PA_DDR:3;  
 
//---- PB ---------
#define  PB_M	 	PB_DDR
 _Bool   PB0_M  @PB_DDR:0;  
 _Bool   PB1_M  @PB_DDR:1;  
 _Bool   PB2_M  @PB_DDR:2;  
 _Bool   PB3_M  @PB_DDR:3;  
 _Bool   PB4_M  @PB_DDR:4;  
 _Bool   PB5_M  @PB_DDR:5;  
 _Bool   PB6_M  @PB_DDR:6;  
 _Bool   PB7_M  @PB_DDR:7;  

//---- PC ---------
#define  PC_M		 PC_DDR
 _Bool   PC1_M  @PC_DDR:1;  
 _Bool   PC2_M  @PC_DDR:2;  
 _Bool   PC3_M  @PC_DDR:3;  
 _Bool   PC4_M  @PC_DDR:4;  
 _Bool   PC5_M  @PC_DDR:5;  
 _Bool   PC6_M  @PC_DDR:6;  
 _Bool   PC7_M  @PC_DDR:7;   

//---- PD ---------
#define  PD_M	 PD_DDR
 _Bool   PD0_M  @PD_DDR:0;
 _Bool   PD1_M  @PD_DDR:1;  
 _Bool   PD2_M  @PD_DDR:2;  
 _Bool   PD3_M  @PD_DDR:3;  
 _Bool   PD4_M  @PD_DDR:4;  
 _Bool   PD5_M  @PD_DDR:5;  
 _Bool   PD6_M  @PD_DDR:6;  
 _Bool   PD7_M  @PD_DDR:7;   

//---- PE ---------
#define  PE_M	 PE_DDR
 _Bool   PE5_M  @PE_DDR:5;  

//---- PF ---------
#define  PF_M	 PF_DDR
 _Bool   PF4_M  @PF_DDR:4;  





#endif