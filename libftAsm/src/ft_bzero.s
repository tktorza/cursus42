%define MACH_SYSCALL(nb) 0x2000000 | nb
%define STDOUT 1
%define WRITE 4

section .text
global _ft_bzero
_ft_bzero:
	cmp rdi, 0
	je return
	mov rcx, -1
	mov rbx ,rdi
	jmp loop

loop:
	inc rcx
	cmp rcx, rsi
	je return
	cmp byte[rbx + rcx], 0x00
	je return
	mov byte[rbx + rcx], 0
	jmp loop

return:
	ret