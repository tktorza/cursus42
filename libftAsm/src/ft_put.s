%define WRITE 4
%define STDOUT 1
%define SYSCALL(nb) 0x2000000 | nb

global _ft_put

section .text

_ft_put:
	mov rcx, rdi
	mov rax, 1
	mov rdi, 1
	mov rdx, rax
	mov rax, 0x2000004
	mov rsi, rcx
	syscall
    leave

end:
	ret
