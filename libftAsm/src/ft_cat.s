%define READ 3
%define SYSCALL(nb) 0x2000000 | nb

section .text
global _ft_cat
_ft_cat:
	push rbp
	mov rbp, rsp

read:
	

return:
	mov rsp, rbp
	pop rbp
	ret